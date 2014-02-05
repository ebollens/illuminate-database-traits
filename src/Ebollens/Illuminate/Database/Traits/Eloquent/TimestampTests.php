<?php

namespace Ebollens\Illuminate\Database\Traits\Eloquent;

use Carbon\Carbon;

trait TimestampTests
{
    
    public function existedBetween($startTime = null, $endTime = null)
    {
        $startTime = new Carbon($startTime?: 0);
        $endTime = new Carbon($endTime?: time());
        
        return $this->{static::CREATED_AT} <= $endTime && (!$this->{static::DELETED_AT} || $this->{static::DELETED_AT} > $startTime);
    }
    
    public function existedAt($time = null)
    {
        return $this->existedBetween($time, $time);
    }
    
    public function scopeExistedBetween($query, $startTime = null, $endTime = null, $from = null)
    {
        // joined relations may have more than one created_at/deleted_at column
        // if passed upper case value, then assume it's a model with getTable()
        // else if passed value, then assume it's a table name
        // else, use the current query's from value
        if($from)
            $from = ctype_upper($from{0}) ? (new $from)->getTable() : $from;
        else
            $from = $query->getQuery()->from;
        
        $startTime = new Carbon($startTime?: 0);
        $endTime = new Carbon($endTime?: time());
        
        $query->where($from.'.'.static::CREATED_AT, '<=', $endTime);
        
        if($this->softDelete)
        {
            $query->withTrashed()
                  ->where(function($query) use ($from, $startTime){
                         $query->whereNull($from.'.'.static::DELETED_AT)
                               ->orWhere($from.'.'.static::DELETED_AT, '>', $startTime);
                     });
        }
        
        return $query;    
    }
    
    public function scopeExistedAt($query, $time = null, $from = null)
    {
        return $this->scopeExistedBetween($query, $time, $time, $from); 
    }
    
}