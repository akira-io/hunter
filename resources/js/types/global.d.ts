import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

declare global {
    interface Window {
        Echo: Echo
        Pusher: typeof Pusher
    }
}