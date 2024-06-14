<template>
    <el-select
        v-model="selected_members"
        multiple
        filterable
        clearable
        reserve-keyword
        :placeholder="' '+$t('Select Team Members')"
        :loading="loading"
        @change="$emit('update:modelValue', selected_members)"
        popper-class="fcal_select"
        placement="bottom"
    >
        <el-option
            v-for="host in filteredHosts"
            :key="host.id"
            :disabled="host.disabled"
            :label="host.label"
            :value="host.id"
        />
    </el-select>

</template>

<script>
export default {
    name: 'TeamMemberSelector',
    props: ['modelValue'],
    $emits: ['update:modelValue'],
    data() {
        return {
            hosts: [],
            loading: false,
            selected_members: this.modelValue,
        }
    },
    computed: {
        filteredHosts() {
            return this.hosts.filter(host => !host.deleted_user);
        }
    },
    methods: {
        fetchHosts() {
            this.loading = true;
            this.$get('admin/all-hosts')
                .then(response => {
                    this.hosts = response.hosts;
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    },
    mounted() {
        this.fetchHosts();
    },
}
</script>
