<?php

namespace Modules\Constructions\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Contact;

class Project extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'construction_projects';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'budget' => 'decimal:4',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    
    /**
     * Get the client associated with the project.
     */
    public function client()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
    
    /**
     * Get the business that owns the project.
     */
    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }
    
    /**
     * Get the user who created the project.
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }
} 