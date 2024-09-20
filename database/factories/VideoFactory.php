<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Video;
use Dotenv\Util\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VideoFactory extends Factory
{
    protected $model = Video::class;

    public function definition(): array
    {
        return [
            'video_code' => $this->faker->word(),
            'original_s3_key' => $this->faker->word(),
            'processed_s3_key' => $this->faker->word(),
            'title' => $this->faker->word(),
            'description' => $this->faker->text(),
            'status' => 'processed',
            'metadata' => [
                'duration' => $this->faker->randomNumber(),
                'width' => $this->faker->randomNumber(),
                'height' => $this->faker->randomNumber(),
                'size' => $this->faker->randomNumber(),
            ],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'views' => $this->faker->randomNumber(),

            'user_id' => User::factory(),
        ];
    }
}
