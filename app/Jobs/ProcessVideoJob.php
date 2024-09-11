<?php

namespace App\Jobs;

use App\Models\Video;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Format\Video\X264;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\LaravelFFMpeg\Filters\WatermarkFactory;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ProcessVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Video $video)
    {

    }

    public function handle(): void
    {
        $video = FFMpeg::fromDisk('s3')
            ->open($this->video->video_file_path);
        $resolution = $video->getVideoStream()->getDimensions();
        $height = $resolution->getHeight();
        $width = $resolution->getWidth();

        $video->addWatermark(function (WatermarkFactory $watermark) use ($height, $width) {
            $watermark->fromDisk('s3')
                ->open($this->video->watermark_image_path);
            match ($this->video->watermark_position) {
                'top-left' => $watermark->horizontalAlignment('left')->verticalAlignment('top'),
                'top-right' => $watermark->horizontalAlignment('right')->verticalAlignment('top'),
                'bottom-left' => $watermark->horizontalAlignment('left')->verticalAlignment('bottom'),
                'bottom-right' => $watermark->horizontalAlignment('right')->verticalAlignment('bottom'),
            };

        });

        $video->export()
            ->toDisk('s3')
            ->inFormat(new X264('libmp3lame', 'libx264'))
            ->save($this->video->video_file_path);
    }
}
