<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Informasi Pemilik -->
            <div class="mb-10">
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-2">Informasi Pemilik</h2>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <p class="text-gray-700 dark:text-gray-300">
                        <span class="font-semibold">Nama:</span> {{ $pemilik->nama_pemilik }}
                    </p>
                    <p class="text-gray-700 dark:text-gray-300">
                        <span class="font-semibold">Email:</span> {{ $pemilik->user->email }}
                    </p>
                </div>
            </div>

            <!-- Daftar Toko -->
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Daftar Toko</h2>
            <!-- Daftar Toko -->
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Daftar Toko</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($toko as $store)
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $store->nama_toko }}
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $store->alamat_toko }}
                    </p>

                    <!-- Status Verifikasi dengan ikon dan tooltip -->
                    <div class="flex items-center space-x-2 mt-4">
                        <span class="font-semibold text-gray-700 dark:text-gray-300">Status Verifikasi:</span>
                        <span id="status-{{ $store->id_toko }}" class="{{ $store->is_verified ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="{{ $store->is_verified ? 'green' : 'red' }}" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>{{ $store->is_verified ? 'Terverifikasi' : 'Tidak Terverifikasi' }}</span>
                        </span>
                    </div>

                    <!-- Exp Date -->
                    <div class="mt-4">
                        <span class="font-semibold text-gray-700 dark:text-gray-300">Tanggal Exp Langganan:</span>
                        <p class="text-gray-600 dark:text-gray-400">{{ $store->exp_date_langganan }}</p>
                    </div>

                    <button
                        class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full"
                        onclick="openModal('{{ $store->id_toko }}', '{{ $store->is_verified }}', `{{ \Carbon\Carbon::parse($store->exp_date_langganan)->format('Y-m-d') }}`)">
                        Ubah Status Verifikasi
                    </button>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="storeModal" class="fixed inset-0 hidden bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="relative p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Ubah Status Verifikasi</h3>
                <div class="space-y-4 mx-4 my-2 p-4">
                    <input type="hidden" id="storeIdInput">

                    <!-- Verification Status -->
                    <div>
                        <label for="verificationStatus" class="block text-sm font-medium text-gray-700">Status Verifikasi</label>
                        <select id="verificationStatus" class="w-full border rounded px-4 py-2 mt-1">
                            <option value="1">Terverifikasi</option>
                            <option value="0">Belum Terverifikasi</option>
                        </select>
                    </div>

                    <!-- Start Date (only visible when verified) -->
                    <div class="mt-2">
                        <label for="startDate" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input type="date" id="startDate" class="w-full border rounded px-4 py-2 mt-1" placeholder="Tanggal Mulai" readonly>
                    </div>

                    <!-- Expiration Date -->
                    <div id="expDateWrapper" class="hidden mt-2">
                        <label for="expDate" class="block text-sm font-medium text-gray-700">Tanggal Exp Langganan</label>
                        <input type="date" id="expDate" class="w-full border rounded px-4 py-2 mt-1" placeholder="Tanggal Exp Langganan">
                    </div>
                </div>

                <div class="flex justify-between px-4 py-3 mt-4">
                    <button id="closeModal" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md hover:bg-red-700">
                        Batal
                    </button>
                    <button id="saveStatus" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md hover:bg-green-700">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(storeId, currentStatus, date) {
            document.getElementById('storeIdInput').value = storeId;
            document.getElementById('verificationStatus').value = currentStatus;
            document.getElementById('startDate').value = currentStatus == 1 ? '{{ \Carbon\Carbon::now()->format("Y-m-d" ) }}' : ''; // Show current date when verified
            document.getElementById('expDate').value = date;
            toggleExpDateField(currentStatus);
            document.getElementById('storeModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('storeModal').classList.add('hidden');
            document.getElementById('expDate').value = '';
        }

        function toggleExpDateField(value) {
            const expDateWrapper = document.getElementById('expDateWrapper');
            if (value == 1 || value === '1') {
                expDateWrapper.classList.remove('hidden');
            } else {
                expDateWrapper.classList.add('hidden');
                document.getElementById('expDate').value = '';
            }
        }

        document.getElementById('closeModal').addEventListener('click', closeModal);

        document.getElementById('verificationStatus').addEventListener('change', function() {
            toggleExpDateField(this.value);
        });

        document.getElementById('saveStatus').addEventListener('click', function() {
            const storeId = document.getElementById('storeIdInput').value;
            const status = document.getElementById('verificationStatus').value;
            const expDate = document.getElementById('expDate').value;

            // Validasi
            if (status == 1 && !expDate) {
                alert('Tanggal Exp Langganan wajib diisi untuk status Terverifikasi.');
                return;
            }

            fetch(`/toko/ubah-verifikasi/${storeId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        is_verified: status,
                        exp_date_langganan: expDate
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message); // Menampilkan pesan sukses

                    // Polling untuk memeriksa perubahan status toko
                    const intervalId = setInterval(() => {
                        fetch(`/toko/${storeId}/status`)
                            .then(response => response.json())
                            .then(statusData => {
                                const statusElement = document.getElementById('status-' + storeId);
                                const statusText = statusData.is_verified ? 'Terverifikasi' : 'Tidak Terverifikasi';
                                statusElement.innerText = statusText;
                                statusElement.classList.remove('text-green-600', 'text-red-600');
                                statusElement.classList.add(statusData.is_verified ? 'text-green-600' : 'text-red-600');

                                // Hentikan polling setelah mendapatkan status terbaru
                                clearInterval(intervalId);
                                closeModal();
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Gagal mengambil status terbaru.');
                                clearInterval(intervalId); // Hentikan polling jika gagal
                            });
                    }, 500); // Polling setiap 5 detik
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal mengubah status verifikasi.');
                });
        });
    </script>
</x-app-layout>