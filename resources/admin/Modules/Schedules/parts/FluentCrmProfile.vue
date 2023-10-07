<template>
    <div v-if="crmProfile" class="fcal_schedule_profile_box">
        <div class="fcal_schedule_profile_header">
            <h1>FluentCrm Profile</h1>
        </div>
        <el-skeleton v-if="loading" />

        <div v-else class="fcal_schedule_profile_body" v-html="crmProfile"></div>
    </div>
</template>

<script>

export default {
    name: "FluentCrmProfile",
    props: ['crm_email'],
    data() {
        return {
            loading: false,
            crmProfile: ''
        }
    },
    methods: {
        fetchCrmProfile() {
            this.loading = true;
            this.$get(`schedules/crm-profile/`, {
                crmProfile: this.crm_email
            })
                .then(response => {
                    this.crmProfile = response.crm_profile;
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
    mounted() {
        this.fetchCrmProfile();
    }
}
</script>
