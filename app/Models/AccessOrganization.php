<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessOrganization extends Model
{
    use HasFactory;

    protected $fillable =[
        'orgid',
        'organization',
        'allow_login',
        'allow_inapp_msg',
        'allow_email_msg',
        'conversation_batch',
        'created_by_id',
        'updated_by_id',
    ];

    protected $attributes = [
        'allow_login' => 'N',
        'allow_inapp_msg' => 'N',
        'allow_email_msg' => 'N',
    ];

    public function created_by() 
    {
        return $this->hasOne(User::Class, 'id', 'created_by_id');
    }

    public function updated_by() 
    {
        return $this->hasOne(User::Class, 'id', 'updated_by_id');
    }

    public function active_employee_ids() {
        return $this->hasMany('App\Models\UserDemoJrView', 'organization_key', 'orgid')
            ->whereNull('user_demo_jr_view.date_deleted')
            ->distinct('user_demo_jr_view.guid')
            ->select('user_demo_jr_view.guid');
}

}
