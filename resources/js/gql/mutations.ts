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
        message
    }
`
