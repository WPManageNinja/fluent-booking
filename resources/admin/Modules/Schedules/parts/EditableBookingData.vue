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
                <el-input v-model="booking[data_key]" :type="input_type" />
            </div>
            <el-button :disabled="updating" v-loading="updating" @click="updateData()">Update</el-button>
            <el-button text :disabled="updating" @click="editing = false">Cancel</el-button>
        </div>
    </div>
</template>

<script type="text/babel">
import {EditPen} from "@element-plus/icons-vue";

export default {
    name: 'EditableSpotData',
    $emits: ['dataUpdated'],
    components: {
        EditPen
    },
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
            this.$put(`schedules/${this.spot.id}`, {
                column: this.data_key,
                value: this.booking[this.data_key]
            })
                .then(response => {
                    this.$handleSuccess(response);
                    this.value = this.booking[this.data_key];
                    this.editing = false;
                    this.$emit('dataUpdated', {
                        key: this.data_key,
                        value: this.booking[this.data_key]
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
