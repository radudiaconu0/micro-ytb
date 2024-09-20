<?php

namespace Tests\Feature\GraphQL;

use App\Models\User;
use App\Models\Video;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests;

    private $user;
    private $video;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->video = Video::factory()->create(['video_code' => 'test_video']);
    }

    private string $createCommentMutation = '
        mutation CreateComment($videoCode: String!, $body: String!) {
            createComment(video_code: $videoCode, body: $body) {
                id
                text
                video {
                    video_code
                }
                user {
                name
                }
            }
        }
    ';

    private string $updateCommentMutation = '
        mutation UpdateComment($commentId: ID!, $body: String!) {
            updateComment(comment_id: $commentId, body: $body) {
                id
                text
                user {
                    name
                }
                video {
                video_code
                }
            }
        }
    ';

    private string $deleteCommentMutation = '
        mutation DeleteComment($commentId: ID!) {
            deleteComment(comment_id: $commentId) {
                id
                text
            }
        }
    ';

    public function testCreateComment(): void
    {
        $this->actingAs($this->user);

        $response = $this->graphQL($this->createCommentMutation, [
            'videoCode' => $this->video->video_code,
            'body' => 'Test comment',
        ]);


        $response->assertJsonStructure([
            'data' => [
                'createComment' => [
                    'id',
                    'text',
                    'user',
                    'video',
                ],
            ],
        ]);

        $this->assertDatabaseHas('comments', [
            'text' => 'Test comment',
            'user_id' => $this->user->id,
            'video_id' => $this->video->id,
        ]);
    }

    public function testCreateCommentUnauthenticated(): void
    {
        $response = $this->graphQL($this->createCommentMutation, [
            'videoCode' => $this->video->video_code,
            'body' => 'Test comment',
        ]);

        $response->assertJsonStructure([
            'errors' => [
                ['message'],
            ],
        ]);
    }

    public function testUpdateComment(): void
    {
        $this->actingAs($this->user);

        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'video_id' => $this->video->id,
        ]);

        $response = $this->graphQL($this->updateCommentMutation, [
            'commentId' => $comment->id,
            'body' => 'Updated comment',
        ]);

        $response->assertJsonStructure([
            'data' => [
                'updateComment' => [
                    'id',
                    'text',
                    'user',
                    'video',
                ],
            ],
        ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'text' => 'Updated comment',
        ]);
    }

    public function testUpdateNonExistentComment(): void
    {
        $this->actingAs($this->user);

        $response = $this->graphQL($this->updateCommentMutation, [
            'commentId' => 9999,
            'body' => 'Updated comment',
        ]);

        $response->assertJsonStructure([
            'errors' => [
                ['message'],
            ],
        ]);
    }

    public function testDeleteComment(): void
    {
        $this->actingAs($this->user);

        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'video_id' => $this->video->id,
        ]);

        $response = $this->graphQL($this->deleteCommentMutation, [
            'commentId' => $comment->id,
        ]);

        $response->assertJson([
            'data' => [
                'deleteComment' => [
                    'id' => $comment->id,
                    'text' => $comment->text,
                ],
            ],
        ]);
    }

    public function testDeleteNonExistentComment(): void
    {
        $this->actingAs($this->user);

        $response = $this->graphQL($this->deleteCommentMutation, [
            'commentId' => 9999,
        ]);

        $response->assertJsonStructure([
            'errors' => [
                ['message'],
            ],
        ]);
    }
}
