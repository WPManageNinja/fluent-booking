<template>
    <div class="fcal_section fcal_section_narrow">
        <div v-if="hasSupport('multi_users')" class="fcal_section_header">
            <div class="fcal_title">
                <h3>{{ $t('Calendars') }}</h3>
            </div>
            <div v-if="hasAccess('invite_team_members')" class="fcal_actions">
                <el-button class="fcal_primary_btn" @click="isNewBookingOpen = true">
                    <span>+</span> {{ $t('Add New Host') }}
                </el-button>
            </div>
        </div>
        <div class="fcal_section_body">

            <SkeletonLoader v-if="loading"/>

            <div v-else class="fcal_calendars_wrap">
                <template v-if="calendars.length">
                    <div v-for="calendar in calendars" :key="calendar.id" class="fcal_each_cal">
                        <calendar-event-block @fetchCalendar="getCalendars" :calendar="calendar"/>
                    </div>
                </template>
                <el-empty v-else class="fcal_empty" :description="$t('No Calendars found')"/>
            </div>

            <div class="fcal_right fcal_tm20">
                <pagination :pagination="pagination" @fetch="getCalendars"/>
            </div>
        </div>

        <el-drawer
            v-model="isNewBookingOpen"
            :title="$t('Add New Calendar Host')"
            :zIndex="999"
            label-position="top"
            modal-class="fcal_drawer">
            <div class="fcal_create_new_booking_type_drawer">
                <el-form-item label="Select Host">
                    <HostSelector v-model="user_id"/>
                    <p>{{ $t('AllCalendars/create_host_desc') }}</p>
                </el-form-item>
                <el-button
                    @click="createOneToOneSlot"
                    :disabled="!user_id">
                    <div class="icons-wrap">
                        <el-icon>
                            <User/>
                        </el-icon>
                        <el-icon>
                            <Right/>
                        </el-icon>
                        <div class="icons">
                            <el-icon>
                                <User/>
                            </el-icon>
                        </div>
                    </div>
                    <div class="content">
                        <h3>{{ $t('One-to-One') }}</h3>
                        <h4><strong>{{ $t('One host') }}</strong> <span>{{ $t('with') }}</span> <strong>{{ $t('One invitee') }}</strong></h4>
                        <p>{{ $t('Good for: coffee chats, 1:1 interviews, etc.') }}</p>
                        <el-icon class="icon-right">
                            <Right/>
                        </el-icon>
                    </div>
                </el-button>
                <el-button
                    @click="createGroupSlot"
                    :disabled="!user_id">
                    <div class="icons-wrap">
                        <el-icon>
                            <User/>
                        </el-icon>
                        <el-icon>
                            <Right/>
                        </el-icon>
                        <div class="icons">
                            <el-icon>
                                <User/>
                            </el-icon>
                            <el-icon>
                                <User/>
                            </el-icon>
                        </div>
                    </div>
                    <div class="content">
                        <h3>{{ $t('Group') }}</h3>
                        <h4><strong>{{ $t('One host') }}</strong> <span>{{ $t('with') }}</span> <strong>{{ $t('Group of invitees') }}</strong></h4>
                        <p>{{ $t('Good for: webinars, online classes, etc.') }}</p>
                        <el-icon class="icon-right">
                            <Right/>
                        </el-icon>
                    </div>
                </el-button>
            </div>
        </el-drawer>
    </div>
</template>

<script type="text/babel">
import Pagination from "../../Pieces/Pagination.vue";
import CalendarEventBlock from "./parts/CalendarEventBlock.vue";
import {User, Right} from '@element-plus/icons-vue';
import HostSelector from "../../Pieces/HostSelector";
import SkeletonLoader from "../../Pieces/SkeletonLoader";

export default {
    name: 'AllCalendars',
    components: {
        SkeletonLoader,
        HostSelector,
        User,
        Right,
        Pagination,
        CalendarEventBlock
    },
    data() {
        return {
            calendars: [],
            loading: false,
            pagination: {
                total: 0,
                per_page: 10,
                current_page: 1
            },
            isNewBookingOpen: false,
            user_id: ''
        }
    },
    methods: {
        getCalendars() {
            this.loading = true;
            this.$get('calendars', {
                per_page: this.pagination.per_page,
                page: this.pagination.current_page
            })
                .then(response => {
                    this.calendars = response.calendars.data;
                    this.pagination.total = response.calendars.total;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        createOneToOneSlot() {
            this.$router.push({
                name: 'create_calendar',
                params: {host_id: this.user_id, event_type: 'single'}
            })
        },
        createGroupSlot() {
            this.$router.push({
                name: 'create_calendar',
                params: {host_id: this.user_id, event_type: 'group'}
            })
        }
    },
    mounted() {
        this.$changeTitle('Calendars');
        this.getCalendars();
    }
}
</script>
