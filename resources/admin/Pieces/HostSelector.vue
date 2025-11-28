<template>
    <el-select
        v-model="selected_host_id"
        filterable
        remote
        reserve-keyword
        :placeholder="$t('Search host and select')"
        remote-show-suffix
        :remote-method="fetchHosts"
        :loading="loading"
        @change="$emit('update:modelValue', selected_host_id)"
        popper-class="fcal_select"
        placement="bottom"
    >
        <el-option
            v-for="item in hosts"
            :key="item.id"
            :disabled="item.disabled"
            :label="item.label"
            :value="item.id"
        >
            <div class="fcal_select_title">
                <img :src="item.avatar" :alt="item.label"> {{ item.label }}
            </div>
        </el-option>
    </el-select>

</template>

<script type="text/babel">
export default {
    name: 'HostSelector',
    props: ['modelValue'],
    $emits: ['update:modelValue'],
    data() {
        return {
            loading: false,
            hosts: [],
            selected_host_id: this.modelValue
        }
    },
    methods: {
        fetchHosts(search) {
            this.loading = true;
            this.$get('admin/remaining-hosts', {
                selected_id: this.selected_host_id,
                search: search
            })
                .then(response => {
                    this.hosts = response.hosts;
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        selectHost(host) {
            this.selected_host = host;
            this.$emit('update:modelValue', host);
        }
    },
    mounted() {
        this.fetchHosts();
    },
}
</script>
