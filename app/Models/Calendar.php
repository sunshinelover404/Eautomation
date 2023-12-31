<?php

namespace App\Models;

use CodeIgniter\Model;

class Calendar extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'calendars';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'calname',
        'caldescription',
        'caldavlink',
        'owner',
        'createdby'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];


    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function events()
    {
        return $this->hasMany('App\Models\EventModel', 'calendar_id');
    }
    
}
