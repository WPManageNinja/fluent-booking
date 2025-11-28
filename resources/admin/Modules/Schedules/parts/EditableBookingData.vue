<template>
    <div class="fcal_spot_details_row">
        <div class="fcal_spot_details_label">
            {{ input_label }} <el-icon class="fcal_clickable" @click="editing = !editing"><EditPen /></el-icon>
        </div>
        <div v-if="!editing" class="fcal_spot_details_value">
            {{value || 'N/A'}}
        </div>
        <div v-else class="fcal_spot_details_value fcal_spot_details_editing">
            <div class="fcal_spot_input">
                <el-input v-model="value" :type="input_type" />
            </div>
            <el-button :disabled="updating" v-loading="updating" @click="updateData()" class="fcal_update_btn">
                {{ $t('Update') }}
            </el-button>
            <el-button text :disabled="updating" @click="editing = false">
                {{ $t('Cancel') }}
            </el-button>
        </div>
    </div>
</template>

<script>

export default {
    name: 'EditableSpotData',
    $emits: ['dataUpdated'],
    props: ['booking', 'data_key', 'input_type', 'input_label'],
    data() {
        return {
            editing: false,
            updating: false,
            value: this.booking[this.data_key]
        }
    },
    methods: {
        updateData() {
            this.updating = true;
            this.$put(`schedules/${this.booking.id}`, {
                column: this.data_key,
                value: this.value
            })
                .then(response => {
                    this.$handleSuccess(response);
                    this.booking[this.data_key] = this.value;
                    this.editing = false;
                    this.$emit('dataUpdated', {
                        key: this.data_key,
                        value: this.value
                    });
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.updating = false;
                });
        }
    }
}
</script>
