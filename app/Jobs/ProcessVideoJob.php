<?php

namespace App\Jobs;

use App\Models\Video;
use Exception;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\Point;
use FFMpeg\FFProbe;
use FFMpeg\Filters\Video\VideoFilters;
use FFMpeg\Format\Video\X264;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use ProtoneMedia\LaravelFFMpeg\Exporters\HLSVideoFilters;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\LegacyFilterMapping;
use ProtoneMedia\LaravelFFMpeg\Filters\WatermarkFactory;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Storage;
use Str;

class ProcessVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Video $video)
    {

    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $originalVideoPath = 'videos/original/' . $this->video->original_s3_key;
        $video = FFMpeg::fromDisk('s3')->open($originalVideoPath);

        $videoDimensions = $video->getVideoStream()->getDimensions();
        $videoWidth = $videoDimensions->getWidth();
        $videoHeight = $videoDimensions->getHeight();

        $this->video->metadata = $this->extractFromS3($originalVideoPath);

        if ($this->video->watermark_type === 'text') {
            $this->addTextWatermark($video, $videoWidth, $videoHeight);
        } else {
            $this->addImageWatermark($video, $videoWidth, $videoHeight);
        }

        $processedS3Key = Str::uuid()->toString() . '_' . time() . '.mp4';
        $this->video->processed_s3_key = $processedS3Key;
        $this->video->status = 'processed';
        $this->video->save();
        $video->export()
            ->toDisk('s3')
            ->inFormat(new X264())
            ->save('videos/processed/' . $processedS3Key);
    }


    /**
     * @throws Exception
     */
    public function extractFromS3($s3Key): array
    {
        $ffprobe = FFMpeg::getFFProbe();

        try {
            $media = FFMpeg::fromDisk('s3')->open($s3Key);

            $format = $ffprobe->format($media->getPathfile());
            $streams = $ffprobe->streams($media->getPathfile());
            $videoStream = $streams->videos()->first();
            $audioStream = $streams->audios()->first();

            return [
                'duration' => (float)$format->get('duration'),
                'bit_rate' => (int)$format->get('bit_rate'),
                'format_name' => $format->get('format_name'),
                'width' => $videoStream?->get('width'),
                'height' => $videoStream?->get('height'),
                'codec_name' => $videoStream?->get('codec_name'),
                'frame_rate' => $videoStream ? $this->calculateFrameRate($videoStream->get('r_frame_rate')) : null,
                'audio_codec' => $audioStream?->get('codec_name'),
                'audio_channels' => $audioStream?->get('channels'),
                'audio_sample_rate' => $audioStream?->get('sample_rate'),
            ];
        } catch (Exception $e) {
            \Log::error("Error extracting metadata: " . $e->getMessage());
            throw $e;
        }
    }

    private function calculateFrameRate($rFrameRate): ?float
    {
        if (str_contains($rFrameRate, '/')) {
            list($numerator, $denominator) = explode('/', $rFrameRate);
            return $denominator != 0 ? round($numerator / $denominator, 2) : null;
        }
        return (float)$rFrameRate;
    }

    private function addImageWatermark($video, $videoWidth, $videoHeight): void
    {
        $watermarkPath = 'watermarks/' . $this->video->watermark_content;
        $watermarkContent = Storage::disk('s3')->get($watermarkPath);

        $manager = new ImageManager(new Driver());
        $image = $manager->read($watermarkContent);

        $watermarkWidth = $image->width();
        $watermarkHeight = $image->height();


        $videoAspectRatio = $videoWidth / $videoHeight;

        $baseScaleFactor = $this->getBaseScaleFactor($videoAspectRatio);

        $targetWidth = $videoWidth * $baseScaleFactor;
        $targetHeight = $videoHeight * $baseScaleFactor;


        $widthRatio = $targetWidth / $watermarkWidth;
        $heightRatio = $targetHeight / $watermarkHeight;


        $scaleFactor = min($widthRatio, $heightRatio);


        $newWidth = $watermarkWidth * $scaleFactor;
        $newHeight = $watermarkHeight * $scaleFactor;


        $resizedImage = $image->resize($newWidth, $newHeight);

        $resizedWatermarkPath = 'watermarks/resized_' . $this->video->watermark_content;
        Storage::disk('s3')->put($resizedWatermarkPath, $resizedImage->toJpeg());

        $padding = max(10, round($videoWidth * 0.01));

        $video->addWatermark(function (WatermarkFactory $watermark) use ($padding, $resizedWatermarkPath)  {
            $watermark->fromDisk('s3')
                ->open($resizedWatermarkPath);
            match ($this->video->watermark_position) {
                'top-left' => $watermark->horizontalAlignment('left')->verticalAlignment('top')->top($padding)->left($padding),
                'top-right' => $watermark->horizontalAlignment('right')->verticalAlignment('top')->top($padding)->right($padding),
                'bottom-left' => $watermark->horizontalAlignment('left')->verticalAlignment('bottom')->bottom($padding)->left($padding),
                'bottom-right' => $watermark->horizontalAlignment('right')->verticalAlignment('bottom')->bottom($padding)->right($padding),
            };
        });
    }

    private function addTextWatermark($video, $videoWidth, $videoHeight): void
    {
        $videoAspectRatio = $videoWidth / $videoHeight;
        $baseScaleFactor = $this->getBaseScaleFactor($videoAspectRatio);

        $fontSize = max(12, round($videoWidth * $baseScaleFactor / 20));

        $padding = max(10, round($videoWidth * 0.01));

        $video->addFilter(function (VideoFilters $filters) use ($fontSize, $padding, $videoWidth, $videoHeight) {
            $fontColor = 'white@0.75';
            $boxColor = 'black@0.5';
            $fontFile = './public/fonts/arial.ttf';

            $filterOptions = [
                'fontfile' => $fontFile,
                'fontsize' => $fontSize,
                'fontcolor' => $fontColor,
                'box' => '1',
                'boxcolor' => $boxColor,
                'boxborderw' => '5',
                'line_spacing' => '5',
            ];

            $filterString = "drawtext=text='" . addslashes($this->video->watermark_content) . "'";
            foreach ($filterOptions as $key => $value) {
                $filterString .= ":{$key}={$value}";
            }

            $position = match ($this->video->watermark_position) {
                'top-left' => ":x={$padding}:y={$padding}",
                'top-right' => ":x=w-tw-{$padding}:y={$padding}",
                'bottom-left' => ":x={$padding}:y=h-th-{$padding}",
                'bottom-right' => ":x=w-tw-{$padding}:y=h-th-{$padding}",
            };

            $filterString .= $position;

            $filters->custom($filterString);
        });
    }

    private function getBaseScaleFactor($videoAspectRatio): float
    {
        if ($videoAspectRatio > 1.78) {
            return 0.10;
        } elseif ($videoAspectRatio < 1) {
            return 0.20;
        } else {
            return 0.15;
        }
    }
}
