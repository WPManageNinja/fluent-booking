<template>
    <div class="fcal_spot_details">
        <div v-if="showing_spots">
            <div :class="'fcal_status_' + showing_spots[0].status" class="fcal_booking_header">
                <div class="fcal_head_title">
                    {{ showing_spots[0].slot_minutes }} minutes meeting with {{ showing_spots[0].first_name }}
                    {{ showing_spots[0].last_name }} @ {{ toCurrentTimezone(showing_spots[0].start_time, 'DD MMM YYYY, hh:mma') }}
                </div>

                <div class="fcal_item_actions">
                    <el-button
                        v-if="showing_spots[0].status != 'cancelled' && showing_spots[0].status != 'completed'"
                        @click="cancelDialog = true"
                        :disabled="updating"
                        type="danger">
                        Cancel Booking
                    </el-button>
                    <confirm message="Are you sure you want to change the status?"
                             v-if="(showing_spots[0].status == 'completed' || showing_spots[0].happening_status) && showing_spots[0].status != 'no_show'"
                             placement="top-start" @yes="updateScheduleStatus('no_show')">
                        <template #reference>
                            <el-button
                                v-loading="updating"
                                :disabled="updating"
                                type="default">
                                Mark as No Show
                            </el-button>
                        </template>
                    </confirm>
                </div>
            </div>
            <el-row :gutter="30">
                <el-col :md="24" :sm="24">
                    <el-row :gutter="30">
                        <el-col :md="8" :sm="12">
                            <div class="fcal_spot_details_row">
                                <div class="fcal_spot_details_label">
                                    Location
                                </div>
                                <div class="fcal_spot_details_value">
                                    <div class="fcal_location" v-html="showing_spots[0].location"></div>
                                </div>
                            </div>
                        </el-col>
                        <el-col :md="8" :sm="12">
                            <div class="fcal_spot_details_row">
                                <div class="fcal_spot_details_label">
                                    Booked at
                                </div>
                                <div class="fcal_spot_details_value">
                                    <span>{{ toCurrentTimezone(showing_spots[0].created_at, 'DD MMM YYYY, hh:mma') }}</span>
                                </div>
                            </div>
                        </el-col>
                        <el-col :md="8" :sm="12">
                            <div class="fcal_spot_details_row">
                                <div class="fcal_spot_details_label">
                                    Status
                                </div>
                                <div class="fcal_spot_details_value">
                                    <span :class="'fcal_'+showing_spots[0].status">{{ showing_spots[0].status }}</span>
                                </div>
                            </div>
                        </el-col>
                        <el-col :md="8" :sm="12">
                            <div v-if="showing_spots[0].source_url" class="fcal_spot_details_row">
                                <div class="fcal_spot_details_label">
                                    Booking URL
                                </div>
                                <div class="fcal_spot_details_value">
                                    <a target="_blank" rel="nofollow" :href="showing_spots[0].source_url">{{showing_spots[0].source_url}}</a>
                                </div>
                            </div>
                        </el-col>
                    </el-row>
                    <hr v-if="showing_spots[0].slot.event_type === 'group'"/>
                    <el-row :gutter="30" v-for="(showing_spot, index) in showing_spots" :key="index">
                        <el-col v-if="showing_spot.slot?.event_type === 'group'">
                            <h3>{{ guestName(showing_spot, index+1) }}</h3>
                        </el-col>
                        <el-col :md="8" :sm="12">
                            <div class="fcal_spot_details_row">
                                <div class="fcal_spot_details_label">
                                    Email
                                </div>
                                <div class="fcal_spot_details_value">
                                    {{ showing_spot.email }}
                                </div>
                            </div>
                        </el-col>
                        <el-col v-if="showing_spot.phone" :md="8" :sm="12">
                            <div class="fcal_spot_details_row">
                                <div class="fcal_spot_details_label">
                                    Phone
                                </div>
                                <div class="fcal_spot_details_value">
                                    {{ showing_spot.phone }}
                                </div>
                            </div>
                        </el-col>
                        <el-col :md="8" :sm="12">
                            <div class="fcal_spot_details_row">
                                <div class="fcal_spot_details_label">
                                    Invitee Time Zone
                                </div>
                                <div class="fcal_spot_details_value">
                                    {{ showing_spot.person_time_zone }}
                                </div>
                            </div>
                        </el-col>
                        <el-col :md="8" :sm="12" v-if="showing_spot.message" class="fcal_spot_details_row">
                            <div class="fcal_spot_details_label">
                                Comments by Invitee
                            </div>
                            <div class="fcal_spot_details_value">
                                {{ showing_spot.message || 'N/A' }}
                            </div>
                        </el-col>
                        <el-col :md="8" :sm="12">
                            <editable-spot-data 
                                input_type="textarea"
                                input_label="Internal Note"
                                data_key="internal_note"
                                @dataUpdated="handleDataUpdated"
                                :spot="showing_spot">
                            </editable-spot-data>
                        </el-col>
                    </el-row>
                </el-col>
            </el-row>

            <hr/>
            <h3 class="fcal_section_title">Meeting Activities</h3>

            <booking-activities :booking_id="spot_id"/>

        </div>
        <el-skeleton v-if="fetching_spot"></el-skeleton>
        <el-dialog width="30%" title="Cancel Meeting" v-model="cancelDialog">
            <div style="text-align: center;">
                <h3>{{ showing_spots[0].slot.title }}</h3>
                <p>with <b>{{ showing_spots[0].first_name }} {{ showing_spots[0].last_name }}</b></p>
                <p>{{ toCurrentTimezone(showing_spots[0].start_time, 'MMMM D, YYYY hh:mma') }} -
                    {{ toCurrentTimezone(showing_spots[0].end_time, 'MMMM D, YYYY hh:mma') }}</p>

                <p style="text-align: left;">Please confirm that you would like to cancel this event. A cancellation
                    email will also go out to the invitee.</p>
                <el-input type="textarea" v-model="cancel_reason" placeholder="Reason for cancellation"></el-input>

            </div>

            <template #footer>
              <span class="dialog-footer">
                <el-button @click="cancelDialog = false">No, Don't cancel</el-button>
                <el-button v-loading="updating" :disabled="updating" type="primary" @click="cancelEvent()">
                  Yes, Cancel
                </el-button>
              </span>
            </template>
        </el-dialog>
    </div>
</template>

<script type="text/babel">
import EditableSpotData from "./EditableSpotData.vue";
import Confirm from "../../../Pieces/Confirm.vue";
import BookingActivities from './_BookingActivities.vue';

export default {
    name: 'SpotInfo',
    props: ['spot_id', 'spot'],
    $emits: ['spotFetched'],
    components: {
        EditableSpotData,
        Confirm,
        BookingActivities
    },
    data() {
        return {
            updating: false,
            fetching_spot: false,
            showing_spots: this.spot,
            cancelDialog: false,
            cancel_reason: ''
        }
    },
    watch: {
        spot_id() {
            this.showing_spots = null;
            this.fetching_spot = true;
            setTimeout(() => {
                this.showing_spots = this.spot;
                this.fetching_spot = false;
            }, 150);
        }
    },
    computed: {
        guestName() {
            return (spot, index) => {
                return spot.first_name + ' ' + spot.last_name + ' (guest #' + index + ')';
            }
        }
    },
    methods: {
        cancelEvent() {
            this.updateScheduleStatus('cancelled');
        },
        updateScheduleStatus(new_status) {
            this.updating = true;
            const data = {
                column: 'status',
                value: new_status
            };

            if (new_status == 'cancelled') {
                data.cancel_reason = this.cancel_reason;
            }

            const updatePromises = this.showing_spots.map(spot => {
                return this.$put(`schedules/${spot.id}`, data)
                    .then(response => {
                        data.message = response.message;
                        spot.status = new_status;
                        spot.happening_status = '';
                        this.cancelDialog = false;
                    })
            });

            Promise.all(updatePromises)
                .then(() => {
                    this.$notify.success(data.message);
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.updating = false;
                });
        },
        fetchSchedule() {
            this.fetching_spot = true;
            this.$get(`schedules/${this.spot_id}`)
                .then(response => {
                    this.showing_spots = response.schedule;
                    this.$emit('spotFetched', response.schedule);
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.fetching_spot = false;
                });
        },
        handleDataUpdated(data) {
            if (this.spot) {
                this.spot[data.key] = data.value;
            }
        }
    },
    mounted() {
        if (!this.spot) {
            this.fetchSchedule();
        }
    }
}
</script>
