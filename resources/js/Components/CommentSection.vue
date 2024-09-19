<script setup>
import { ref, onMounted, watchEffect } from 'vue';
import { useQuery, useMutation } from '@vue/apollo-composable';
import gql from 'graphql-tag';
import {useAuthStore} from "../stores/authStore.ts";

const props = defineProps({
    videoCode: {
        type: String,
        required: true
    }
});

const store = useAuthStore();

const comments = ref([]);
const loading = ref(false);
const error = ref(null);
const hasNextPage = ref(true);
const currentPage = ref(1);
const newCommentBody = ref('');
const editingComment = ref(null);
const isDarkMode = ref(false);
const observerTarget = ref(null);

const FETCH_COMMENTS = gql`
  query FetchComments($videoCode: String!, $page: Int!) {
    videoComments(video_code: $videoCode, first: 10, page: $page) {
      data {
        id
        body
        created_at
        user {
          id
          name
        }
      }
      paginatorInfo {
        currentPage
        lastPage
      }
    }
  }
`;

const CREATE_COMMENT = gql`
  mutation CreateComment($videoCode: String!, $body: String!) {
    createComment(video_code: $videoCode, body: $body) {
      id
      body
      created_at
      user {
        id
        name
      }
    }
  }
`;

const DELETE_COMMENT = gql`
  mutation DeleteComment($commentId: ID!) {
    deleteComment(comment_id: $commentId) {
      id
    }
  }
`;

const UPDATE_COMMENT = gql`
  mutation UpdateComment($commentId: ID!, $body: String!) {
    updateComment(comment_id: $commentId, body: $body) {
      id
      body
    }
  }
`;

const CREATE_REPLY = gql`
  mutation CreateReply($commentId: ID!, $body: String!) {
    createReply(comment_id: $commentId, body: $body) {
      id
      body
      created_at
      user {
        id
        name
      }
    }
  }
`;

const FETCH_REPLIES = gql`
  query FetchReplies($commentId: ID!, $page: Int!) {
    commentReplies(comment_id: $commentId, first: 5, page: $page) {
      data {
        id
        body
        created_at
        user {
          id
          name
        }
      }
      paginatorInfo {
        lastPage
      }
    }
  }
`;

const { mutate: createCommentMutation } = useMutation(CREATE_COMMENT);
const { mutate: deleteCommentMutation } = useMutation(DELETE_COMMENT);
const { mutate: updateCommentMutation } = useMutation(UPDATE_COMMENT);
const { mutate: createReplyMutation } = useMutation(CREATE_REPLY);

let fetchComments;

onMounted(() => {
    setupCommentsFetching();
    setupInfiniteScroll();
});

const setupCommentsFetching = () => {
    const { result, loading: commentsLoading, error: commentsError, refetch } = useQuery(FETCH_COMMENTS, {
        videoCode: props.videoCode,
        page: currentPage.value
    });

    fetchComments = refetch;

    watchEffect(() => {
        if (result.value) {
            console.log('API Response:', result.value); // For debugging

            if (result.value.videoComments) {
                const newComments = result.value.videoComments.data || [];
                comments.value = [...comments.value, ...newComments];

                const paginatorInfo = result.value.videoComments.paginatorInfo;
                if (paginatorInfo) {
                    const { currentPage: fetchedPage, lastPage } = paginatorInfo;
                    hasNextPage.value = fetchedPage < lastPage;
                    currentPage.value = fetchedPage + 1;
                } else {
                    console.log('Paginator info is null, assuming no more pages');
                    hasNextPage.value = false;
                }
            } else {
                console.log('No comments found for this video');
                comments.value = [];
                hasNextPage.value = false;
            }
        }
        loading.value = commentsLoading.value;
        if (commentsError.value) {
            console.error('GraphQL Error:', commentsError.value);
            error.value = 'Failed to load comments';
        }
    });

    loadComments();
};

const loadComments = async () => {
    if (loading.value || !hasNextPage.value) return;

    try {
        await fetchComments({
            videoCode: props.videoCode,
            page: currentPage.value
        });
    } catch (e) {
        console.error('Error fetching comments:', e);
        error.value = 'Failed to load comments';
    }
};

const setupInfiniteScroll = () => {
    const observer = new IntersectionObserver(
        (entries) => {
            if (entries[0].isIntersecting && !loading.value) {
                loadComments();
            }
        },
        { threshold: 1.0 }
    );

    if (observerTarget.value) {
        observer.observe(observerTarget.value);
    }
};

const submitComment = async () => {
    if (!newCommentBody.value.trim()) return;
    try {
        const { data } = await createCommentMutation({
            variables: {
                videoCode: props.videoCode,
                body: newCommentBody.value
            }
        });
        comments.value.unshift(data.createComment);
        newCommentBody.value = '';
    } catch (e) {
        console.error('Error creating comment:', e);
        error.value = 'Failed to create comment';
    }
};

const deleteComment = async (commentId) => {
    try {
        await deleteCommentMutation({
            variables: { commentId }
        });
        comments.value = comments.value.filter(c => c.id !== commentId);
    } catch (e) {
        console.error('Error deleting comment:', e);
        error.value = 'Failed to delete comment';
    }
};

const editComment = (comment) => {
    editingComment.value = { ...comment };
};

const updateComment = async () => {
    if (!editingComment.value) return;
    try {
        const { data } = await updateCommentMutation({
            variables: {
                commentId: editingComment.value.id,
                body: editingComment.value.body
            }
        });
        const index = comments.value.findIndex(c => c.id === data.updateComment.id);
        if (index !== -1) {
            comments.value[index] = { ...comments.value[index], ...data.updateComment };
        }
        editingComment.value = null;
    } catch (e) {
        console.error('Error updating comment:', e);
        error.value = 'Failed to update comment';
    }
};

const cancelEdit = () => {
    editingComment.value = null;
};

const replyToComment = async (comment) => {
    const replyBody = prompt('Enter your reply:');
    if (!replyBody) return;

    try {
        const { data } = await createReplyMutation({
            variables: {
                commentId: comment.id,
                body: replyBody
            }
        });
        if (!comment.replies) {
            comment.replies = [];
        }
        comment.replies.unshift(data.createReply);
    } catch (e) {
        console.error('Error creating reply:', e);
        error.value = 'Failed to create reply';
    }
};

const toggleReplies = async (comment) => {
    comment.showReplies = !comment.showReplies;
    if (comment.showReplies && !comment.replies) {
        await loadReplies(comment);
    }
};

const loadReplies = async (comment) => {
    try {
        const { data } = await useQuery(FETCH_REPLIES, {
            variables: {
                commentId: comment.id,
                page: 1
            }
        });
        comment.replies = data.commentReplies.data || [];
        comment.repliesHasNextPage = data.commentReplies.paginatorInfo?.lastPage > 1;
        comment.repliesCurrentPage = 1;
    } catch (e) {
        console.error('Error loading replies:', e);
        error.value = 'Failed to load replies';
    }
};

const loadMoreReplies = async (comment) => {
    try {
        const { data } = await useQuery(FETCH_REPLIES, {
            variables: {
                commentId: comment.id,
                page: comment.repliesCurrentPage + 1
            }
        });
        comment.replies = [...comment.replies, ...(data.commentReplies.data || [])];
        comment.repliesHasNextPage = data.commentReplies.paginatorInfo?.lastPage > comment.repliesCurrentPage + 1;
        comment.repliesCurrentPage++;
    } catch (e) {
        console.error('Error loading more replies:', e);
        error.value = 'Failed to load more replies';
    }
};

const isCurrentUser = (userId) => {
    return true;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

</script>
<template>
    <div :class="['comment-section', { 'dark': isDarkMode }]">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-lg">
            <div v-if="loading && comments.length === 0" class="text-center text-gray-600 dark:text-gray-400">
                <svg class="animate-spin h-8 w-8 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading comments...
            </div>
            <div v-else-if="error" class="text-red-500 text-center">{{ error }}</div>
            <div v-else-if="comments.length === 0" class="text-center text-gray-600 dark:text-gray-400">
                No comments yet. Be the first to comment!
            </div>
            <div v-else>
                <div v-for="comment in comments" :key="comment.id" class="mb-6 last:mb-0">
                    <div class="bg-gray-100 dark:bg-zinc-800 p-4 rounded-lg">
                        <p class="text-gray-800 dark:text-gray-200 mb-2">{{ comment.body }}</p>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            <span class="font-semibold">{{ comment.user.name }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ formatDate(comment.created_at) }}</span>
                        </div>
                        <div class="flex space-x-2 mb-2">
                            <button v-if="isCurrentUser(comment.user.id)" @click="editComment(comment)" class="text-blue-500 hover:text-blue-600 transition duration-300">Edit</button>
                            <button v-if="isCurrentUser(comment.user.id)" @click="deleteComment(comment.id)" class="text-red-500 hover:text-red-600 transition duration-300">Delete</button>
                            <button @click="toggleReplies(comment)" class="text-gray-600 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition duration-300">
                                {{ comment.showReplies ? 'Hide Replies' : 'Show Replies' }}
                            </button>
                            <button @click="replyToComment(comment)" class="text-green-500 hover:text-green-600 transition duration-300">Reply</button>
                        </div>
                    </div>
                    <div v-if="comment.showReplies && comment.replies" class="ml-8 mt-4">
                        <div v-for="reply in comment.replies" :key="reply.id" class="mb-4 last:mb-0">
                            <div class="bg-gray-50 dark:bg-zinc-700 p-3 rounded-lg">
                                <p class="text-gray-800 dark:text-gray-200 mb-2">{{ reply.body }}</p>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    <span class="font-semibold">{{ reply.user.name }}</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ formatDate(reply.created_at) }}</span>
                                </div>
                                <div class="flex space-x-2">
                                    <button v-if="isCurrentUser(reply.user.id)" @click="editComment(reply)" class="text-blue-500 hover:text-blue-600 transition duration-300">Edit</button>
                                    <button v-if="isCurrentUser(reply.user.id)" @click="deleteComment(reply.id)" class="text-red-500 hover:text-red-600 transition duration-300">Delete</button>
                                </div>
                            </div>
                        </div>
                        <div v-if="comment.repliesHasNextPage" class="mt-2">
                            <button @click="loadMoreReplies(comment)" class="text-blue-500 hover:text-blue-600 transition duration-300">Load More Replies</button>
                        </div>
                    </div>
                </div>
                <div ref="observerTarget" class="h-10 w-full">
                    <div v-if="loading" class="text-center text-gray-600 dark:text-gray-400">
                        Loading more comments...
                    </div>
                </div>
            </div>
            <div class="mt-6">
                <textarea v-model="newCommentBody" placeholder="Write a comment..." class="w-full p-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-zinc-800 dark:text-white"></textarea>
                <button @click="submitComment" class="mt-2 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition duration-300">Submit Comment</button>
            </div>
        </div>
        <div v-if="editingComment" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-xl max-w-lg w-full">
                <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Edit Comment</h3>
                <textarea v-model="editingComment.body" class="w-full p-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-zinc-900 dark:text-white mb-4"></textarea>
                <div class="flex justify-end space-x-2">
                    <button @click="cancelEdit" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition duration-300">Cancel</button>
                    <button @click="updateComment" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-300">Update</button>
                </div>
            </div>
        </div>
    </div>
</template>
