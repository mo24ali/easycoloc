<x-app-layout>
    {{ 'add colloc' }}


    {{-- <form method="post" action="/addcolloc">
        <button id="add-coloc-btn" class="bg-green-500 rounded">
            add a colloc
        </button>
    </form> --}}
    <form method="post" action="/addcollocation">
        <label>Name : </label>
        <input name="collocationName" type = "text">
        <label>Members : </label>
        <input name="collocationMembers" type = "text">
        <label>Cost : </label>
        <input name="collocationCost" type = "text">
        <button>
            submit
        </button>
    </form>





    <script></script>
</x-app-layout>
