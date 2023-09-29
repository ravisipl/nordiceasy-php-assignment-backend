<?php

namespace App\Console\Commands;

use DB;
use Carbon\Carbon;
use App\Models\State;
use App\Models\Status;
use App\Helpers\Helper;
use App\Models\Tickets;
use App\Models\TicketLog;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TicketStatusLog;
use Illuminate\Console\Command;
use \Illuminate\Support\Facades\Queue;



class UpdateTicketStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change the status of tickets created 3 days before';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $PendingForConfId = ($r = Status::getStatusByTitle('Pending for Confirmation'))? $r->id : null;
        $ClosedId = ($r = Status::getStatusByTitle('Closed'))? $r->id : null;

        $tickets = Tickets::where('status_id', $PendingForConfId)->get();

        foreach ($tickets as $ticket) {
            $resolvedAt = $ticket->resolved_at;

            if (!$resolvedAt) {
                continue;
            }

            $now = Carbon::now();
            $hours = $this->calculateWorkingHours($resolvedAt , $now);

            if ($hours >= 24) {
                DB::beginTransaction();
                $ticket->status_id = $ClosedId;
                $start = $ticket->created_at;
                $workingHours = $this->calculateWorkingHours($start, $now);
                $ticket->tat = $workingHours;
                $ticket->closed_at = $now;
                if($ticket->save()){

                    $ticketNumber = $ticket->ticket_number;
                    // Store ticket entry in ticket status log table
                    $ticketStatusLog = new TicketStatusLog;
                    $ticketStatusLog->guid = Str::uuid()->toString();
                    $ticketStatusLog->ticket_id = $ticket->id;
                    $ticketStatusLog->status_id = $ticket->status_id;
                    $ticketStatusLog->created_by =  null;
                    $ticketStatusLog->save();
    
                    // Logic to store an entry into ticket log for STATUS
                    $ticketLog = new TicketLog;
                    $ticketLog->guid = Str::uuid()->toString();
                    $ticketLog->ticket_id = $ticket->id;
                    $ticketLog->ticket_number = $ticketNumber;
                    $actMsg = config('constants.TICKET_LOG_MSG.STATUS');
                    $actMsg = str_replace("{number}", $ticketNumber, $actMsg);
                    $actMsg = str_replace("{status}", 'Closed', $actMsg);
                    $ticketLog->activity = $actMsg;
                    $ticketLog->created_by = null;
                    $ticketLog->save();
                    DB::commit();
         
                    $raisedBy = $ticket->raisedBy;
                    
                    $ticketNumber = $ticket->ticket_number;
                    $subject = config('constants.TICKET_EMAIL_SUBJECT.TICKET_STATUS_UPDATE_SUBJECT');  
                    $subject = str_replace("{number}", $ticketNumber, $subject);
                    $data = [];      
                    $data['ticket_number'] = $ticketNumber;
                    $data['new_status'] = 'Closed';
                    $data['status'] = 'Closed';
                    $data['department_name'] = ($r = $ticket->getSupportingDepartment) ? $r->name : null;
                    $data['category'] = ($r = $ticket->getCategory) ? $r->name : null;;
                    $data['priority'] = $ticket->priority;
                    $data['estimated_time'] = $ticket->eta . ' ' . $ticket->eta_unit;   
                    $data['resolvance_time'] = Helper::formateResolvancyTime($ticket->resolvance_tat);

                    // Helper::sendEmail('mailtemplates.tickets.ticket_status_changed', $data, $raisedBy->email,  $raisedBy->full_name, $subject);
                    $data['to_name'] = $raisedBy->full_name;
                    Queue::connection('database')->push(new SendEmailJob('mailtemplates.tickets.ticket_status_changed', $data, $raisedBy->email,  $raisedBy->full_name, $subject));


                   
                }
            }
        }

        // $this->info('Ticket statuses updated successfully.');
    }

    private function calculateWorkingHours($createdAt, $now){
        $totalMinutes = 0;
        $createdAt = Carbon::parse($createdAt); // Convert string to Carbon instance
        for($date = clone $createdAt; $date->lte($now); $date->addDay()) {
            // Determine the start and end of the working hours for the current day
            $startOfWork = $date->copy()->setTime(10, 0);
            $endOfWork = $date->copy()->setTime(19, 0);
    
            if($date->isWeekday()) {
                if ($date->isSameDay($createdAt)) {
                    // If the current day is the first day, adjust the start of work to the created_at time
                    $startOfWork = $createdAt;
                }
    
                if ($date->isSameDay($now)) {
                    // If the current day is the last day, adjust the end of work to the now time
                    $endOfWork = $now;
                }
    
                // Add the difference in minutes to the total, only if the start of work is before the end of work
                if ($startOfWork->lessThan($endOfWork)) {
                    $totalMinutes += $startOfWork->diffInMinutes($endOfWork);
                }
            }
        }
    
        // Convert the minutes to hours, preserving the fractional part
        return round($totalMinutes / 60, 2);
    }
}
