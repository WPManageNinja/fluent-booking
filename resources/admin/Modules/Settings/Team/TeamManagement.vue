<template>
    <div style="margin-bottom: 25px;" class="fcal_settings_body_inner fcal_settings_general">
        <div class="fcal_configure_integration_card">
            <div class="fcal_configure_integration_card_header">
                <div class="left">
                    <div class="img-box">
                        <el-icon style="font-size: 30px;">
                            <TeamIcon/>
                        </el-icon>
                    </div>
                    <div class="content">
                        <h3>{{ $t('Team') }}</h3>
                        <p>{{ $t('TeamManagement/description') }}</p>
                    </div>
                </div>
                <div v-if="isEnabled" class="right">
                    <el-button type="primary" @click="initShow()">
                        <el-icon><Plus /></el-icon>
                        <span>{{ $t('Team Member') }}</span>
                    </el-button>
                </div>
            </div>
            <template v-if="isEnabled">
                <el-skeleton animated v-if="loading"></el-skeleton>
                <div v-else class="fcal_configure_integration_body">
                    <div class="fcal_integration_items">
                        <div class="fcal_integration_item" v-for="member in members" :key="member.id">
                            <div class="fcal_card_wrap">
                                <div class="fcal_integration_icon">
                                    <img class="general_integration_logo" :src="member.avatar"/>
                                </div>
                                <div class="fcal_card_item_details">
                                    <h3>{{ member.name }}</h3>
                                    <ul class="event_triggers">
                                        <template v-if="member.is_admin">
                                            <li style="color: green;">
                                                <el-icon>
                                                    <Lock/>
                                                </el-icon>
                                                <span>{{ $t('Administrator') }}</span>
                                            </li>
                                        </template>
                                        <template v-else-if="member.permissions">
                                            <li v-for="permission in member.permissions" :key="permission">
                                                <el-icon>
                                                    <Lock/>
                                                </el-icon>
                                                <span>{{ getPermissionName(permission) }}</span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                            <div v-if="!member.is_admin" class="fcal_card_actions">
                                <el-button
                                    size="small"
                                    type="success"
                                    @click="initEdit(member)"
                                >
                                    <el-icon>
                                        <Edit/>
                                    </el-icon>
                                </el-button>
    
                                <el-popconfirm
                                    popper-class="fcal_confirm_dialog"
                                    :title="$t('Are you sure to delete this?')"
                                    :confirm-button-text="$t('Yes')"
                                    :cancel-button-text="$t('No')"
                                    @confirm="deleteTeamMember(member)">
                                    <template #reference>
                                        <el-button v-if="!member.is_calendar_user" type="danger" size="small"
                                                   class="fcal_danger_btn">
                                            <el-icon>
                                                <Delete/>
                                            </el-icon>
                                        </el-button>
                                    </template>
                                </el-popconfirm>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <ProNotice v-else/>
        </div>
        <el-dialog
            v-model="showModal"
            :append-to-body="true"
            :close-on-click-modal="false"
            :before-close="() => { showModal = false; editingMember = null; }"
            :title="$t('Edit Team Member')"
            width="50%"
            class="fcal_dialog fcal_add_team_member_dialog"
        >
            <el-form v-if="editingMember" label-position="top">
                <el-form-item :label="$t('Access Permissions for this user')">
                    <el-checkbox-group class="fcal_checkable_lined" v-model="editingMember.permissions">
                        <el-checkbox v-for="(permission, permissionKey) in permission_sets" :key="permissionKey"
                                     :disabled="permissionKey == 'manage_own_calendar'"
                                     :label="permissionKey"
                                     class="fcal_checkbox"
                        >
                            {{ permission }} <span
                            v-if="permissionKey == 'manage_own_calendar'">{{ $t('(Required Permission)') }}</span>
                        </el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button type="primary" @click="updatePermissions()">{{ $t('Update Access Permissions') }}</el-button>
            </template>
        </el-dialog>
        <el-dialog
            v-model="showAddModal"
            :append-to-body="true"
            :close-on-click-modal="false"
            :before-close="() => { showAddModal = false; showAddModal = null; }"
            :title="$t('Add Team Member')"
            width="50%"
            class="fcal_dialog fcal_add_team_member_dialog"
        >
            <el-form v-if="showAddModal" label-position="top">
                <el-form-item :label="$t('Select Member')">
                    <HostSelector v-model="user_id"/>
                </el-form-item>
                <el-form-item :label="$t('Access Permissions for this user')">
                    <el-checkbox-group class="fcal_checkable_lined" v-model="addingMember.permissions">
                        <el-checkbox v-for="(permission, permissionKey) in permission_sets" :key="permissionKey"
                                     :disabled="permissionKey == 'manage_own_calendar'"
                                     :label="permissionKey"
                                     class="fcal_checkbox"
                        >
                            {{ permission }} <span
                            v-if="permissionKey == 'manage_own_calendar'">{{ $t('(Required Permission)') }}</span>
                        </el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button type="primary" @click="addMember()">{{ $t('Add Team Member') }}</el-button>
            </template>
        </el-dialog>
    </div>
</template>

<script>
import TeamIcon from '@/Components/Icons/TeamIcon.vue';
import HostSelector from "@/Pieces/HostSelector";
import ProNotice from '@/Components/Common/ProNotice.vue';

export default {
    name: 'TeamManagement',
    props: ['disabled'],
    components: {
    HostSelector,
    TeamIcon,
    ProNotice
},
    data() {
        return {
            members: [],
            permission_sets: {},
            loading: false,
            addingMember: {
                permissions: ['manage_own_calendar']
            },
            showAddModal: false,
            editingMember: null,
            showModal: false,
            saving: false,
            user_id: '',
            isEnabled: !this.disabled && this.appVars.has_pro
        }
    },
    methods: {
        fetch() {
            this.loading = true;
            this.$get('settings/team')
                .then(response => {
                    this.members = response.members;
                    this.permission_sets = response.permission_sets;
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        initShow() {
            this.addingMember.permissions = ['manage_own_calendar'];
            this.showAddModal = true;
        },
        getPermissionName(permission) {
            // replace _ from permission name
            const replaceUnderscore = permission.replace(/_/g, ' ');
            return this.$t(replaceUnderscore);
        },
        initEdit(member) {
            this.editingMember = member;
            this.showModal = true;
        },
        updatePermissions() {
            this.saving = true;
            this.$post('settings/team', {
                user_id: this.editingMember.id,
                permissions: this.editingMember.permissions
            })
                .then(response => {
                    this.fetch();
                    this.$notify.success(response.message);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                    this.showModal = false;
                });
        },
        addMember() {
            this.saving = true;
            this.$post('settings/team', {
                user_id: this.user_id,
                permissions: this.addingMember.permissions
            })
                .then(response => {
                    this.fetch();
                    this.$notify.success(response.message);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                    this.showAddModal = false;
                });
        },
        deleteTeamMember(member) {
            this.saving = true;
            this.$del('settings/team/'+member.id,)
                .then(response => {
                    this.fetch();
                    this.$notify.success(response.message);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.saving = false;
                });
        }
    },
    mounted() {
        if (this.isEnabled) {
            this.fetch();
        }
    }
}
</script>
