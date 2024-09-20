import gql from 'graphql-tag'

export const LOGIN_MUTATION = gql`
    mutation login($email: String!, $password: String!) {
        login(email: $email, password: $password) {
            access_token
            token_type
            expires_in
        }
    }
`


export const REGISTER_MUTATION = gql`
    mutation Register($name: String!, $email: String!, $password: String!, $password_confirmation: String!) {
        register(name: $name, email: $email, password: $password, password_confirmation: $password_confirmation) {
            access_token
            expires_in
            token_type
        }
    }
`

export const LOGOUT_MUTATION = gql`
    mutation Logout {
        logout {
            message
        }
    }
`

export const CREATE_COMMENT = gql`
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

export const DELETE_COMMENT = gql`
    mutation DeleteComment($commentId: ID!) {
        deleteComment(comment_id: $commentId) {
            id
        }
    }
`;

export const UPDATE_COMMENT = gql`
    mutation UpdateComment($commentId: ID!, $body: String!) {
        updateComment(comment_id: $commentId, body: $body) {
            id
            text
        }
    }
`;
