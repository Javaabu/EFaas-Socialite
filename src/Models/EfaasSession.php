<?php

namespace Javaabu\EfaasSocialite\Models;

use Illuminate\Database\Eloquent\Model;
use Javaabu\EfaasSocialite\Contracts\EfaasSessionContract;

class EfaasSession extends Model implements EfaasSessionContract
{
    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('efaas.database_connection'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('efaas.table_name'));
        }

        parent::__construct($attributes);
    }



    public function logOut()
    {
        // first destroy the laravel session
        session()->getHandler()->destroy($this->laravel_session_id);

        // then destroy self
        $this->delete();
    }
}
