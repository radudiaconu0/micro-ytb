<?php

namespace App\Jobs;

use App\Models\Video;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\Point;
use FFMpeg\Filters\Video\VideoFilters;
use FFMpeg\Format\Video\X264;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\LaravelFFMpeg\Exporters\HLSVideoFilters;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\LegacyFilterMapping;
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
        \Log::info('Adding text watermark');
        $video = FFMpeg::fromDisk('s3')
            ->open('videos/' . $this->video->video_file_path);
        $videoDimensions = $video->getVideoStream()->getDimensions();

        $videoWidth = $videoDimensions->getWidth();
        $videoHeight = $videoDimensions->getHeight();

        $scaleFactor = 0.15;
        $watermarkWidth = $videoWidth * $scaleFactor;
        $watermarkHeight = $watermarkWidth;

        $position = new Point($videoWidth - $watermarkWidth - 10, $videoHeight - $watermarkHeight - 10);


        if ($this->video->watermark_type === 'text') {
            // get resolution of video to adjust font size
            $font_size = $videoWidth / 20;
            $video->addFilter(function (VideoFilters $filters) use ($font_size) {
                $filter = 'text=' . $this->video->watermark_text . ":fontfile=./public/fonts/arial.ttf:fontsize={$font_size}:fontcolor=white:x=10";
                $filters->custom("drawtext=$filter");

                match ($this->video->watermark_position) {
                    'top-left' => $filters->custom("drawtext=$filter:x=10:y=10"),
                    'top-right' => $filters->custom("drawtext=$filter:x=(W-tw-10):y=10"),
                    'bottom-left' => $filters->custom("drawtext=$filter:x=10:y=(H-th-10)"),
                    'bottom-right' => $filters->custom("drawtext=$filter:x=(W-tw-10):y=(H-th-10)"),
                };
            });
        } else {
            $video->addWatermark(function (WatermarkFactory $watermark) use ($watermarkWidth, $watermarkHeight) {
                $watermark->fromDisk('s3')
                    ->open($this->video->watermark_image_path);
                match ($this->video->watermark_position) {
                    'top-left' => $watermark->horizontalAlignment('left')->verticalAlignment('top'),
                    'top-right' => $watermark->horizontalAlignment('right')->verticalAlignment('top'),
                    'bottom-left' => $watermark->horizontalAlignment('left')->verticalAlignment('bottom'),
                    'bottom-right' => $watermark->horizontalAlignment('right')->verticalAlignment('bottom'),
                };
                $watermark->bottom(10)->right(10)->width($watermarkWidth)->height($watermarkHeight);
            });
        }


        $video->export()
            ->toDisk('s3')
            ->inFormat(new X264())
            ->save($this->video->video_file_path);
    }
}
