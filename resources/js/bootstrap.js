import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
try {
	const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
	if (pusherKey) {
		const isHttps = (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https';
		window.Echo = new Echo({
			broadcaster: 'pusher',
			key: pusherKey,
			cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
			wsHost: import.meta.env.VITE_PUSHER_HOST || undefined,
			wsPort: import.meta.env.VITE_PUSHER_PORT ? Number(import.meta.env.VITE_PUSHER_PORT) : undefined,
			wssPort: import.meta.env.VITE_PUSHER_PORT ? Number(import.meta.env.VITE_PUSHER_PORT) : undefined,
			forceTLS: isHttps,
			enabledTransports: isHttps ? ['wss', 'ws'] : ['ws', 'wss'],
		});
	}
} catch (e) {
	console.warn('Echo initialization skipped:', e);
}
