<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;
    /**
      * @desc The attributes that are mass assignable.
      * @var array
    */
    protected $fillable = [
        'client_id',
        'email',
        'phone_number',
        'name',
        'comment',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Get ticket by user id
    public static function getClient($perPage, $skip, $search, $sortField, $sortOrder){
        $query = self::query();

        // Global serach functionality
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email ', 'like', "%$search%")
                    ->orWhere('phone_number', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('comment', 'like', "%$search%");
                
            });
        }

        // Sorting
        if ($sortField && $sortOrder) {
            if ($sortField === 'email') {
                $query->orderBy('email', $sortOrder);
            }elseif ($sortField === 'phone_number') {
                $query->orderBy('phone_number', $sortOrder);
            } elseif ($sortField === 'name') {
                $query->orderBy('name', $sortOrder);
            } elseif ($sortField === 'comment') {
                $query->orderBy('comment', $sortOrder);
            } else {
                $query->orderBy($sortField, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc'); // Use 'created_at' for default sorting
        }
        $query->whereNull('deleted_at'); // Filter out deleted records
        if ($perPage > 0) {
            $query->skip($skip)->take($perPage);
        }

        return $query->get();
    }

    // Check if client email id already exist
    public static function emailExist($email, $id=null){
        $query = self::where('email', $email);
        if($id){
            $query = $query->where('id', '!=', $id);
        }
        return $query->first();
    }
    
    
}
