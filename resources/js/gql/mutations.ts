import gql from 'graphql-tag'

export const LOGIN_MUTATION = gql`
    mutation Login($email: String!, $password: String!) {
        login(email: $email, password: $password) {
            access_token
            expires_in
            token_type
        }
    }
`

export const REGISTER_MUTATION = gql`
    mutation Register($name: String!, $email: String!, $password: String!) {
        register(name: $name, email: $email, password: $password) {
            access_token
            expires_in
            token_type
        }
    }
`
