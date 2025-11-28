<template>
    <el-select
        v-model="selected_members"
        multiple
        filterable
        clearable
        reserve-keyword
        :placeholder="' '+ placeholder"
        @change="$emit('update:modelValue', selected_members)"
        popper-class="fcal_select"
        placement="bottom"
    >
        <el-option
            v-for="host in filteredHosts"
            :key="host.id"
            :disabled="host.disabled"
            :label="host.label"
            :value="host.id">
            <div class="fcal_select_title">
                <img :src="host.avatar" :alt="host.label"> {{ host.label }}
            </div>
        </el-option>
    </el-select>

</template>

<script>
export default {
    name: 'TeamMemberSelector',
    props: ['modelValue', 'modelPlaceholder'],
    $emits: ['update:modelValue'],
    data() {
        return {
            hosts: this.appVars.all_hosts,
            selected_members: this.modelValue,
            placeholder: this.modelPlaceholder || this.$t('Select Team Members')
        }
    },
    computed: {
        filteredHosts() {
            return this.hosts.filter(host => !host.deleted_user);
        }
    }
}
</script>
