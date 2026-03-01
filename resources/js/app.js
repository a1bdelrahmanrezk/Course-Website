import './bootstrap';
import Alpine from 'alpinejs';

// Prevent multiple Alpine instances
if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}
