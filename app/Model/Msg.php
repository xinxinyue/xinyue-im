<?php

declare (strict_types=1);
namespace App\Model;

use Hyperf\DbConnection\Model\Model;
/**
 * @property int $id 
 * @property int $source_id 
 * @property string $body 
 * @property int $receive_id 
 * @property int $type 
 * @property string $create_time 
 * @property string $update_time
 */
class Msg extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'msg';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];


    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'source_id' => 'integer', 'receive_id' => 'integer', 'type' => 'integer'];

    public static function insertMsg($body, $sourceId = 0, $receiveId = 0, $type = 0)
    {
        $model = new self();
        $model->body = $body;
        $model->source_id = $sourceId;
        $model->receive_id = $receiveId;
        $model->type = $type;
        return $model->save();
    }
}