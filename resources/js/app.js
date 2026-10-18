import { toast } from 'vanilla-sonner';
import 'vanilla-sonner/style.css';

// Expose toast globally for console, Alpine.js, and inline scripts
window.toast = toast;

function handleToastDispatch(detail) {
    if (!detail) return;
    const type = detail.type || 'success';
    const message = detail.message || detail.title || 'Notification';
    const description = detail.description || '';

    const options = description ? { description, duration: 4000 } : { duration: 4000 };

    if (typeof toast[type] === 'function') {
        toast[type](message, options);
    } else {
        toast(message, options);
    }
}

// Global browser window event listener
window.addEventListener('toast', (event) => {
    handleToastDispatch(event.detail);
});

// Livewire 3 Event Hooks
document.addEventListener('livewire:init', () => {
    window.Livewire.on('toast', (payload) => {
        const data = Array.isArray(payload) ? payload[0] : payload;
        handleToastDispatch(data);
    });

    // Intercept unsuccessful request failures globally
    window.Livewire.hook('request', ({ fail }) => {
        fail(({ status, content, preventDefault }) => {
            toast.error('Action Failed', {
                description: `Network or server error (Status: ${status || 500})`,
                duration: 5000,
            });
        });
    });
});
