import gql from "graphql-tag";

export const CURRENT_USER_QUERY = gql`
    query {
        me {
            id
            name
            email
        }
    }
`



export const FETCH_VIDEO = gql`
    query FetchVideo($video_code: String!) {
        fetchVideo(video_code: $video_code) {
            url
            title
            views
            description
            user {
                name
            }
            thumbnails {
                thumbnail_url
            }
            created_at,
            video_code
        }
    }
`;

export const GET_VIDEOS = gql`
    query GetVideos($page: Int!, $perPage: Int!) {
        myVideos(page: $page, first: $perPage) {
            data {
                title
                video_code
                description
                status
                created_at
                user {
                    name
                }
                thumbnails {
                    thumbnail_url
                }
            }
            paginatorInfo {
                currentPage
                lastPage
                total
                perPage
            }
        }
    }
`;

export const VIDEO_QUERY = gql`
    query GetVideos($first: Int!, $page: Int) {
        feedVideos(first: $first, page: $page) {
            data {
                video_code,
                views,
                url,
                title,
                description,
                user {
                    name
                },
                thumbnails {
                    thumbnail_url,
                    width,
                    height,
                    size
                },
                created_at
            },
            paginatorInfo {
                currentPage,
                lastPage,
                total,
                perPage
            }
        }
    }
`;

export const FETCH_COMMENTS = gql`
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
export const SEARCH_VIDEO_QUERY = gql`
    query SearchVideos($search: String!) {
        searchVideos(first: 10, page: 1, query: $search) {
            data {
                video_code,
                views
                url
                title,
                description,
                thumbnails {
                    thumbnail_url
                }
                created_at
            }
        }
    }
`;
