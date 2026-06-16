import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const VueSweetAlert2 = {
  install(app) {
    const swal = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
      }
    });

    // Buat method yang lebih user-friendly untuk Composition API
    app.config.globalProperties.$swal = Swal; // Untuk Full Modal
    app.config.globalProperties.$toast = swal; // Untuk Notifikasi Kecil (Toast)
    
    // Sediakan fungsi inject untuk Composition API
    app.provide('swal', Swal);
    app.provide('toast', swal);
  }
};

export default VueSweetAlert2;
