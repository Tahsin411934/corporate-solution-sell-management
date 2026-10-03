<div class="flex space-x-2 justify-center">
    <button onclick="{{ $edit }}({{ $id }})"
        class="bg-blue-900 text-white px-2 py-1 rounded text-sm hover:bg-blue-600 mr-2">
        <i class="fa fa-pencil"></i>
    </button>

    <button onclick="{{ $delete }}({{ $id }})"
        class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600">
        <i class="fa fa-trash"></i>
    </button>
</div>