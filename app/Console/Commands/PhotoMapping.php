<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\v1\Photo;
use App\Models\v1\ResultEvent;

class PhotoMapping extends Command{
    /**
     * 控制台命令名称
     *
     * @var string
     */
    protected $signature = 'photo:mapping {race_id}';

    /**
     * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Map photos information';


    /**
     * 执行控制台命令
     *
     * @return mixed
     */
    public function handle()
    {
        $race_id = $this->argument('race_id');
        $photos = Photo::where('race_id', $race_id)->where('is_mapped', '2')->get();

        if ($photos) {
            // $act_listener = []; $i=1;
            foreach ($photos as $photo) {
                $file_info = Photo::ansPhoto($photo->filename);

                if ($result_event = ResultEvent::getMatching($race_id, $file_info['result'])) {
                    if($result_event->runner) {
                        $photo->runner_id = $result_event->runner->id;
                        $photo->result_event_id = $result_event->id;
                        $photo->is_mapped = 1;
                        $photo->save();

                        /*$act_listener = [
                            'i' => 1,
                            'c' => $i++
                        ];*/
                    }
                }
            }
            // $act_listener && Log::info('[PhotoUpdatedSuccess] '.json_encode($act_listener));
        }
    }
}