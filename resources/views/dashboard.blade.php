<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Card Total Pelanggan -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Total Pelanggan</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalPelanggan }}</p>
                </div>
            </div>

            <!-- Pencarian Pelanggan -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="flex flex-wrap  items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200  w-full md:w-1/3">Pelanggan</h3>
                    <button onclick='openModaladd()' class="mt-2 px-4 py-2 bg-blue-500 text-white rounded w-full md:w-1/3">Tambah Pelanggan</button>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2 w-full md:w-1/3">Pencarian Pelanggan</h3>

                <form method="GET" action="{{ route('pemilik.search') }}">
                    <input type="text" name="query" placeholder="Cari pelanggan..." class="border rounded p-2 w-full">
                    <button type="submit" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded">Cari</button>
                </form>
            </div>

            @if(isset($pemilik) && $pemilik->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Hasil Pencarian</h3>
                <table class="w-full border-collapse border border-gray-300 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left text-gray-800 dark:text-gray-200">Nama Pemilik</th>
                            <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left text-gray-800 dark:text-gray-200">Email</th>
                            <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-center text-gray-800 dark:text-gray-200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pemilik as $item)
                        <tr class="border border-gray-300 dark:border-gray-700">
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-800 dark:text-gray-200">{{ $item->nama_pemilik }}</td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-800 dark:text-gray-200">{{ $item->user->email }}</td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-center">
                                <button onclick='openModaledit()' class="text-yellow-500">Ubah</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @elseif(isset($pemilik))
            <p class="text-gray-800 dark:text-gray-200">Tidak ada hasil yang ditemukan.</p>
            @endif

        </div>

        <!-- Modal Edit User -->
        <div id="editUserModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h2 class="text-lg font-bold mb-4">Edit User</h2>
                <form id="editUserForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="userId" name="id_user">

                    <div class="mb-2">
                        <label for="email" class="block text-sm font-medium">Email</label>
                        <input type="email" id="email" name="email" class="w-full border rounded p-2" autocomplete="email">
                    </div>

                    <div class="mb-2">
                        <label for="password" class="block text-sm font-medium">Password</label>
                        <input type="password" id="password" name="password" class="w-full border rounded p-2" autocomplete="password">
                    </div>

                    <div class="mb-4">
                        <label for="nama_pemilik" class="block text-sm font-medium">Nama Pemilik</label>
                        <input type="text" id="nama_pemilik" name="nama_pemilik" class="w-full border rounded p-2">
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeModaledit()" class="px-4 py-2 bg-gray-500 text-white rounded">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="addOwnerModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h2 class="text-lg font-bold mb-4">Tambah Pemilik</h2>
                <form id="addOwnerForm" method="POST">
                    @csrf

                    <div class="mb-2">
                        <label for="nama_pemilik" class="block text-sm font-medium">Nama Pemilik</label>
                        <input type="text" id="nama_pemilik" name="nama_pemilik" class="w-full border rounded p-2">
                    </div>

                    <div class="mb-2">
                        <label for="email" class="block text-sm font-medium">Email</label>
                        <input type="email" id="email" name="email" class="w-full border rounded p-2" autocomplete="email">
                    </div>

                    <div class="mb-2">
                        <label for="password" class="block text-sm font-medium">Password</label>
                        <input type="password" id="password" name="password" class="w-full border rounded p-2">
                    </div>

                    <div class="mb-4">
                        <label for="konfirmasi_password" class="block text-sm font-medium">Konfirmasi Password</label>
                        <input type="password" id="konfirmasi_password" name="konfirmasi_password" class="w-full border rounded p-2">
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeModaladd()" class="px-4 py-2 bg-gray-500 text-white rounded">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded">Tambah</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function openModaledit(user) {
            document.getElementById('editUserModal').classList.remove('hidden');
            document.getElementById('userId').value = user.id_user;
            document.getElementById('email').value = user.user.email;
            document.getElementById('password').value = ''; // Kosongkan password untuk keamanan
            document.getElementById('nama_pemilik').value = user.nama_pemilik;

            // Ubah action form untuk mengarah ke route update
            document.getElementById('editUserForm').action = `/user/${user.id_user}`;
        }

        function closeModaledit() {
            document.getElementById('editUserModal').classList.add('hidden');
        }
        function openModaladd() {
            document.getElementById('addOwnerModal').classList.remove('hidden');
        }
        function closeModaladd() {
            document.getElementById('addOwnerModal').classList.add('hidden');
        }

    </script>
</x-app-layout>