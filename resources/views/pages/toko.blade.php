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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($toko as $store)
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $store->nama_toko }}
                        </h3>

                        <p class="text-gray-600 dark:text-gray-400">
                            {{ $store->alamat_toko }}
                        </p>

                        <p id="status-{{ $store->id_toko }}" class={{ $store->is_verified?"text-green-600 dark:text-green-400" :"text-red-600 dark:text-red-400" }}>
                            {{ $store->is_verified ? 'Terverifikasi' : 'Tidak Terverifikasi' }}
                        </p>

                        <button
                            class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full"
                            onclick="openModal('{{ $store->id_toko }}', '{{ $store->is_verified }}')">
                            Ubah Status Verifikasi
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="storeModal" class="fixed inset-0 hidden bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="relative p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Ubah Status Verifikasi</h3>
                <div class="mt-2">
                    <input type="hidden" id="storeIdInput">
                    <select id="verificationStatus" class="mt-3 w-full border rounded p-2">
                        <option value="1">Terverifikasi</option>
                        <option value="0">Belum Terverifikasi</option>
                    </select>
                </div>
                <div class="flex justify-between px-4 py-3">
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
        function openModal(storeId, currentStatus) {
            document.getElementById('storeIdInput').value = storeId;
            document.getElementById('verificationStatus').value = currentStatus;
            document.getElementById('storeModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('storeModal').classList.add('hidden');
        }

        document.getElementById('closeModal').addEventListener('click', closeModal);

        document.getElementById('saveStatus').addEventListener('click', function () {
            const storeId = document.getElementById('storeIdInput').value;
            const status = document.getElementById('verificationStatus').value;

            fetch(`/toko/ubah-verifikasi/${storeId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ is_verified: status })
            })
            .then(response => response.json())
            .then(data => {
                const statusText = status == 1 ? 'Terverifikasi' : 'Tidak Terverifikasi';
                const statusElement = document.getElementById('status-' + storeId);

                statusElement.innerText = statusText;
                statusElement.classList.remove('text-green-500', 'text-red-500');
                statusElement.classList.add(status == 1 ? 'text-green-500' : 'text-red-500');

                closeModal();
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengubah status verifikasi.');
            });
        });
    </script>
</x-app-layout>
