<template>
    <el-drawer
      v-model="showModal"
      :title="title"
      :zIndex="999"
      label-position="top"
      modal-class="fcal_drawer">
      <div class="fcal_clone_event_drawer">
        <el-form-item :label="label">
          <el-select
            v-model="selectedEventId"
            :placeholder="placeholder"
            popper-class="fcal_select"
            placement="bottom"
            :no-match-text="$t('No Data match')"
            :no-data-text="$t('No Data')">
            <el-option-group
              v-for="cal in eventLists"
              :key="cal.id"
              :label="cal.title">
              <el-option
                v-for="event in cal.options"
                :disabled="event.id == eventId"
                :key="event.id"
                :label="event.title"
                :value="event.id">
              </el-option>
            </el-option-group>
          </el-select>
          <p>{{ helpText }}</p>
        </el-form-item>
        <SaveButton :saving="saving" :disabled="!selectedEventId" :label="buttonLabel" @click="handleCloneEvent"/>
      </div>
    </el-drawer>
</template>

<script>
import SaveButton from '../Buttons/SaveButton.vue';
export default {
    name: 'CloneDrawer',
    props: {
        isOpen: Boolean,
        eventId: Number,
        saving: Boolean,
        title: String,
        label: String,
        placeholder: String,
        helpText: String,
        buttonLabel: String,
        eventLists: Array,
    },
    components: {
        SaveButton,
    },
    data() {
        return {
            showModal: this.isOpen,
            selectedEventId: null
        };
    },
    watch: {
        showModal(val) {
            this.$emit('update:isOpen', val);
        }
    },
    methods: {
        handleCloneEvent() {
            this.$emit('update:isOpen', false);
            this.$emit('clone', this.selectedEventId);
        },
    }
};
</script>