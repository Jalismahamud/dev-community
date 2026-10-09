import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
try {
	const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
	if (reverbKey) {
		const isHttps = (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https';
		window.Echo = new Echo({
			broadcaster: 'reverb',
			key: reverbKey,
			wsHost: import.meta.env.VITE_REVERB_HOST ?? '127.0.0.1',
			wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
			wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
			forceTLS: isHttps,
			enabledTransports: isHttps ? ['wss'] : ['ws'],
		});
	}
} catch (e) {
	console.warn('Reverb initialization skipped:', e);
}
