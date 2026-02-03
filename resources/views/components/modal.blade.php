<!-- MODAL DELETE -->
<div 
  x-show="showDeleteModal"
  x-transition.opacity
  class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
  style="display: none;"
>
  <div class="bg-white dark:bg-boxdark rounded-xl shadow-lg w-full max-w-md p-6">
    
    <!-- Icon + Title -->
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 flex items-center justify-center rounded-full bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-black dark:text-white">
        Konfirmasi Hapus
      </h3>
    </div>

    <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">
      Yakin ingin menghapus guru ini?  
      Tindakan ini tidak dapat dibatalkan.
    </p>

    <!-- Buttons -->
    <div class="flex justify-end gap-3">
      <button 
        @click="closeDeleteModal()"
        class="px-4 py-2 rounded-lg border border-stroke dark:border-strokedark text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-boxdark-2"
      >
        Batal
      </button>

      <button 
        @click="confirmDelete()"
        class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm"
      >
        Hapus
      </button>
    </div>
  </div>
</div>
