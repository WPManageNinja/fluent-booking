<template>
    <div class="fcal_create_calendar_body">
        <div class="fcal_create_calendar_form">
            <div class="fcal_create_calendar_form_header">
                <h2> <UsersIcon/> {{ $t('Assignment') }} </h2>
            </div>
            <div class="fcal_create_calendar_form_body">
                <el-form label-position="top">
                    <el-form-item :label="$t('Assign Member')">
                        <el-select
                            v-model="settings.team_members"
                            @change="validateTeamMembers"
                            multiple
                            :placeholder="$t('Select')"
                            popper-class="fcal_select">
                            <el-option
                                v-for="host in all_hosts"
                                :key="host.id"
                                :label="host.name"
                                :value="host.id"
                            />
                        </el-select>
                    </el-form-item>
                    <el-form-item :label="$t('Team Members')">
                        <div class="fcal_team_members">
                            <div v-if="!loading" v-for="member in teamMembers" :key="member.id" class="fcal_team_member">
                                <div class="fcal_card_wrap">
                                    <div class="fcal_team_member_icon">
                                        <img :src="member.avatar"/>
                                    </div>
                                    <h3>{{ member.name }}</h3>
                                </div>
                                <div class="fcal_card_actions">
                                    <el-button
                                        @click="goToCalendarSetting(member.calendar_id)">
                                        <el-icon><Edit/></el-icon>
                                    </el-button>
                                    <el-button
                                        v-if="teamMembers.length > 1"
                                        type="danger"
                                        @click="removeTeamMember(member.id)">
                                        <el-icon><Delete/></el-icon>
                                    </el-button>
                                </div>
                            </div>
                            <div v-else>
                                <el-skeleton :rows="3" animated/>
                            </div>
                        </div>
                    </el-form-item>
                </el-form>
            </div>
            <div class="fcal_create_calendar_form_footer">
                <SaveButton :saving="saving" :label="$t('Save Changes')" @click="saveSettings"/>
            </div>
        </div>
    </div>
</template>

<script>
import UsersIcon from "../../../Components/Icons/UsersIcon";
import SaveButton from "@/Components/Buttons/SaveButton";
import { Edit, Delete } from '@element-plus/icons-vue';
export default {
    name: '_Assignment',
    components: {
        SaveButton,
        UsersIcon,
        Edit,
        Delete
    },
    props: ['calendar_event'],
    data() {
        return {
            loading: false,
            saving: false,
            all_hosts: [],
            teamMembers: [],
            settings: this.calendar_event.settings
        }
    },
    watch: {
        'settings.team_members': function(updatedValue) {
            this.updateTeamMembers(updatedValue);
        }
    },
    methods: {
        goToCalendarSetting(calendarId) {
            this.$router.push({
                name: 'calendar_settings',
                params: { calendar_id: calendarId }
            })
        },
        validateTeamMembers(updatedValue) {
            if (!updatedValue.length) {
                this.settings.team_members = [this.all_hosts[0].id];
            }
        },
        updateTeamMembers(updatedValue) {
            this.teamMembers = updatedValue.map((id) => {
                return this.all_hosts.find(host => host.id === id);
            });
        },
        removeTeamMember(id) {
            this.settings.team_members = this.settings.team_members.filter(memberId => memberId !== id);
            this.saveSettings();
        },
        getAllHosts() {
            this.loading = true;
            this.$get('admin/all-hosts')
                .then(response => {
                    this.all_hosts = response.hosts;
                    this.updateTeamMembers(this.settings.team_members);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        saveSettings() {
            this.saving = true;
            this.$post('calendars/' + this.calendar_event.calendar_id + '/events/' + this.calendar_event.id + '/assignments', {
                calendar_id: this.calendar_event.calendar_id,
                team_members: this.settings.team_members
            })
                .then(response => {
                    this.$handleSuccess(response);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
    },
    mounted() {
        this.getAllHosts();
    }
}
</script>
