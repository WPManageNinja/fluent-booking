<script>

    export let field;
    export let form;
    import Select from 'svelte-select';

</script>


<div class="fcal_input_wrap fcal_input_multi_select_wrap">
    <Select
            multiple={true}
            clearable={false}
            items={field.options}
            bind:value={form[field.name]}
    />
</div>

