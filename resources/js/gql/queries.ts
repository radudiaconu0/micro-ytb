import gql from "graphql-tag";

export const ME = gql`
    query Me {
        me {
            id
            name
            email
        }
    }
`;


export const VIDEO_FEED = gql`
    query VideoFeed {
        videoFeed {
            id
            title
            description
            videoUrl
            thumbnailUrl
            createdAt
            updatedAt
            user {
                id
                name
            }
        }
    }
`;
