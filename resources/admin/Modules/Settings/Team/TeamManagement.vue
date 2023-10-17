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
                        <h3>Team</h3>
                        <p>Grant Team Members Access to FluentBookings for Calendar and Booking Management.</p>
                    </div>
                </div>
                <div class="right">
                    <el-button type="primary" @click="addingMember = true">+ Team Member</el-button>
                </div>
            </div>
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
                                            <span>Administrator</span>
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

                            <el-button v-if="!member.is_calendar_user" type="danger" size="small"
                                       class="fcal_danger_btn">
                                <el-icon>
                                    <Delete/>
                                </el-icon>
                            </el-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <el-dialog
            v-model="showModal"
            :append-to-body="true"
            :close-on-click-modal="false"
            :before-close="() => { showModal = false; editingMember = null; }"
            title="Edit Team Member"
            width="50%">
            <el-form v-if="editingMember" label-position="top">
                <el-form-item label="Access Permissions for this user">
                    <el-checkbox-group class="fcal_checkable_lined" v-model="editingMember.permissions">
                        <el-checkbox v-for="(permission, permissionKey) in permission_sets" :key="permissionKey"
                                     :disabled="permissionKey == 'manage_own_calendar'"
                                     :label="permissionKey">
                            {{ permission }} <span
                            v-if="permissionKey == 'manage_own_calendar'">(Required Permission)</span>
                        </el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button type="primary" @click="updatePermissions()">Update Access Permissions</el-button>
            </template>
        </el-dialog>
    </div>
</template>

<script type="text/babel">
import TeamIcon from '@/Components/Icons/TeamIcon.vue';
import {Edit, Lock, Delete} from '@element-plus/icons-vue';

export default {
    name: 'TeamManagement',
    components: {TeamIcon, Edit, Lock, Delete},
    data() {
        return {
            members: [],
            permission_sets: {},
            loading: false,
            addingMember: false,
            editingMember: null,
            showModal: false,
            saving: false
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
        getPermissionName(permission) {
            // replace _ from permission name
            return permission.replace(/_/g, ' ');
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
        }
    },
    mounted() {
        this.fetch();
    }
}
</script>
