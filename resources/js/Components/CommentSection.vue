<script setup>
import {onMounted, ref, watchEffect} from 'vue';
import {useMutation, useQuery} from '@vue/apollo-composable';
import gql from 'graphql-tag';
import {useAuthStore} from "../stores/authStore.ts";
import {formatDistanceToNow} from "date-fns";

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
const newCommentBody = ref("");
const editingComment = ref(null);
const isDarkMode = ref(false);
const observerTarget = ref(null);

const FETCH_COMMENTS = gql`
  query FetchComments($videoCode: String!, $page: Int!) {
    videoComments(video_code: $videoCode, first: 10, page: $page) {
      data {
        id
        text
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
      text
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
      text
    }
  }
`;



const {mutate: createCommentMutation} = useMutation(CREATE_COMMENT);
const {mutate: deleteCommentMutation} = useMutation(DELETE_COMMENT);
const {mutate: updateCommentMutation} = useMutation(UPDATE_COMMENT);

let fetchComments;

onMounted(() => {
    setupCommentsFetching();
    setupInfiniteScroll();
});

const setupCommentsFetching = () => {
    const {result, loading: commentsLoading, error: commentsError, refetch} = useQuery(FETCH_COMMENTS, {
        videoCode: props.videoCode,
        page: currentPage.value
    });

    fetchComments = refetch;

    watchEffect(() => {
        if (result.value) {
            if (result.value.videoComments) {
                const newComments = result.value.videoComments.data || [];

                newComments.forEach(newComment => {
                    const existingIndex = comments.value.findIndex(c => c.id === newComment.id);
                    if (existingIndex !== -1) {

                        comments.value[existingIndex] = newComment;
                    } else {

                        comments.value.push(newComment);
                    }
                });

                comments.value.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

                const paginatorInfo = result.value.videoComments.paginatorInfo;
                if (paginatorInfo) {
                    const {currentPage: fetchedPage, lastPage} = paginatorInfo;
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
        {threshold: 1.0}
    );

    if (observerTarget.value) {
        observer.observe(observerTarget.value);
    }
};

const submitComment = async () => {
    if (!newCommentBody.value.toString().trim()) return;
    console.log('Submitting comment with:', {
        videoCode: props.videoCode,
        body: newCommentBody.value
    });

    try {
        const {data} = await createCommentMutation({
            videoCode: props.videoCode,
            body: newCommentBody.value
        });
        console.log('Comment created:', data);

        if (data && data.createComment) {
            comments.value.unshift(data.createComment);
            newCommentBody.value = "";
        } else {
            console.error('No data returned from createComment mutation:', data);
            error.value = 'Failed to create comment';
        }
    } catch (e) {
        console.error('Error creating comment:', e);
        error.value = 'Failed to create comment';
    }
};

const deleteComment = async (commentId) => {
    try {
        await deleteCommentMutation({
            commentId: commentId
        });
        console.log('Comment deleted:', commentId);
        console.log('Before:', comments.value);
        comments.value = comments.value.filter(c => c.id !== commentId);
    } catch (e) {
        console.error('Error deleting comment:', e);
        error.value = 'Failed to delete comment';
    }
};

const editComment = (comment) => {
    editingComment.value = {...comment};
};

const updateComment = async () => {
    if (!editingComment.value) return;
    try {
        const {data} = await updateCommentMutation({
            commentId: editingComment.value.id,
            body: editingComment.value.text
        });
        console.log('Comment updated:', data.updateComment);
        editingComment.value = null;
    } catch (e) {
        console.error('Error updating comment:', e);
        error.value = 'Failed to update comment';
    }
};

const cancelEdit = () => {
    editingComment.value = null;
};



const isCurrentUser = (userId) => {
    return true;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const distanceToNow = (date) => {
    return formatDistanceToNow(new Date(date), {addSuffix: true});
};

</script>
<template>
    <div :class="['comment-section', { 'dark': isDarkMode }]">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-lg">
            <div class="mb-6" v-if="store.user">
                <textarea v-model="newCommentBody" placeholder="Write a comment..."
                          class="w-full p-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-zinc-800 dark:text-white"></textarea>
                <button @click="submitComment"
                        class="mt-2 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition duration-300">
                    Submit Comment
                </button>
            </div>
            <div v-if="loading && comments.length === 0" class="text-center text-gray-600 dark:text-gray-400">
                <svg class="animate-spin h-8 w-8 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
                        <p class="text-gray-800 dark:text-gray-200 mb-2">{{ comment.text }}</p>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            <span class="font-semibold">{{ comment.user.name }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ distanceToNow(comment.created_at) }}</span>
                        </div>
                        <div class="flex space-x-2 mb-2">
                            <button v-if="isCurrentUser(comment.user.id)" @click="editComment(comment)"
                                    class="text-blue-500 hover:text-blue-600 transition duration-300">Edit
                            </button>
                            <button v-if="isCurrentUser(comment.user.id)" @click="deleteComment(comment.id)"
                                    class="text-red-500 hover:text-red-600 transition duration-300">Delete
                            </button>
                        </div>
                    </div>
                </div>
                <div ref="observerTarget" class="h-10 w-full">
                    <div v-if="loading" class="text-center text-gray-600 dark:text-gray-400">
                        Loading more comments...
                    </div>
                </div>
            </div>

        </div>
        <div v-if="editingComment" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-xl max-w-lg w-full">
                <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Edit Comment</h3>
                <textarea v-model="editingComment.text"
                          class="w-full p-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-zinc-900 dark:text-white mb-4"></textarea>
                <div class="flex justify-end space-x-2">
                    <button @click="cancelEdit"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition duration-300">
                        Cancel
                    </button>
                    <button @click="updateComment"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-300">
                        Update
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
