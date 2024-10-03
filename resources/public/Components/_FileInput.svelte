<div class="fcal_input_content">
    {#if field.label}
        <div class="fcal_input_label">
            {field.label}
            {#if field.required}
                <span>*</span>
            {/if}
        </div>
    {/if}
    <div class="fcal_input_file_wrap">
        <button class="fcal_input_btn" type="button" on:click={()=>openFileUpload()} aria-label="Open File Upload">
            {i18('Choose File')}
        </button>
        {#if uploadedFiles}
            <div class="fcal_uploaded_files">
                {#each uploadedFiles as file}
                    <div class="fcal_uploaded_file">
                        <span>{file.name}</span>
                        <button type="button" class="fcal_delete_file" on:click={()=>deleteFile(file)} aria-label="Delete File">+</button>
                    </div>
                {/each}
            </div>
        {/if}
        {#if uploadingFile}
            <div class="fcal_uploaded_files">
                <div class="fcal_uploaded_file">
                    <span class="fcal_spinner"></span>
                    <span>{uploadingFile.name}</span>
                </div>
            </div>
        {/if}
    </div>
    {#if errorText && hasError}
        <div class="fcal_validation_error">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3 ltr:mr-2 rtl:ml-2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" x2="12" y1="16" y2="12"></line>
                <line x1="12" x2="12.01" y1="8" y2="8"></line>
            </svg>
            <p>{errorText}</p>
        </div>
    {/if}
</div>

<script>
  import { i18, util } from "../util";

    export let slot;
    export let form;
    export let field;
    export let hasError;
    export let validating;

    let errorText = false;
    let uploadedFiles = [];
    let uploadedFileUrls = [];
    let uploadingFile = null;

    $: if (validating || !validating) {
        updateValidationError();
    }

    function updateValidationError() {
        errorText = false;
        if (field.required && !form[field.name]) {
            errorText = i18('This field is required.');
        }
    }

    function openFileUpload() {
        const input = document.createElement('input');
        input.type = 'file';
        input.multiple = field.max_file_allow > 1;
        input.style.display = 'none';

        let fileTypes = field.allow_file_types;
        if (fileTypes.includes('image')) {
            fileTypes = [...fileTypes, 'jpg', 'jpeg', 'gif', 'png', 'bmp', 'webp'];
        }

        input.accept = fileTypes.map(type => `.${type}`).join(',');
        input.addEventListener('change', handleFileUpload);

        document.body.appendChild(input);
        input.click();
    }

    function handleFileUpload(e) {
        const maxFileSize = field.file_size_value * (field.file_size_unit === 'mb' ? 1024 * 1024 : 1024);
        const totalFiles = Math.min(e.target.files.length, field.max_file_allow);
        for (let i = 0; i < totalFiles; i++) {
            const file = e.target.files[i];
            if (file.size <= maxFileSize) {
                uploadFile(file);
                continue;
            }
            hasError = true;
            errorText = i18("File size should be less than") + ' ' + field.file_size_value + ' ' + field.file_size_unit;
        }
    }

    function uploadFile(file) {
        uploadingFile = file;

        util.$post(window.fluentCalendarPublicVars.ajaxurl, {
            event_id: slot.id,
            field_name: field.name,
            file: file,
            action: 'fluent_booking_file_upload'
        })
        .then(response => {
            errorText = false;
            addFile(file, response);
        })
        .catch((error) => {
            console.log(error);
        })
        .finally(() => {
            uploadingFile = null;
        });
    }

    function deleteFile(file) {
        util.$post(window.fluentCalendarPublicVars.ajaxurl, {
            file: file,
            action: 'fluent_booking_file_delete'
        })
        .then(response => {
            removeFile(file, response)
        })
        .catch((error) => {
            console.log(error);
        });
    }

    function addFile(file, response) {
        const uploadedFile = {name: file.name, ...response.file};
        uploadedFiles = [...uploadedFiles, uploadedFile];
        uploadedFileUrls.push(response.file.url);
        form[field.name] = uploadedFileUrls;
    }

    function removeFile(file) {
        uploadedFiles = uploadedFiles.filter(uploadedFile => uploadedFile.url !== file.url);
        uploadedFileUrls = uploadedFileUrls.filter(uploadedFileUrl => uploadedFileUrl !== file.url);
        form[field.name] = uploadedFileUrls;
    }

</script>