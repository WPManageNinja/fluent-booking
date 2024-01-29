<script>
    export let field;
    export let form;
    import Select from 'svelte-select';
</script>

<div class="fcal_input_content">
    {#if field.label}
        <div class="fcal_input_label">
            {field.label}
            {#if field.required}
                <span>*</span>
            {/if}
        </div>
    {/if}
    <div class="fcal_input_wrap fcal_input_multi_select_wrap">
        <Select
            multiple={true}
            clearable={false}
            items={field.options}
            required={field.required}
            bind:value={form[field.name]}
        />
    </div>
</div>

