<template>
    <div class="fcal_spot_details">
        <div v-if="showing_spot">
            <div :class="'fcal_status_' + showing_spot.status" class="fcal_booking_header">
                <div class="fcal_head_title">
                    {{ showing_spot.slot_minutes }} minutes meeting with {{ showing_spot.first_name }}
                    {{ showing_spot.last_name }} @ {{ toCurrentTimezone(showing_spot.start_time, 'DD MMM YYYY, hh:mma') }}
                </div>

                <div class="fcal_item_actions">
                    <el-button
                        v-if="showing_spot.status != 'cancelled' && showing_spot.status != 'completed'"
                        @click="cancelDialog = true"
                        :disabled="updating"
                        type="danger">
                        Cancel Booking
                    </el-button>
                    <confirm message="Are you sure you want to change the status?"
                             v-if="(showing_spot.status == 'completed' || showing_spot.happening_status) && showing_spot.status != 'no_show'"
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
                                    Email
                                </div>
                                <div class="fcal_spot_details_value">
                                    {{ showing_spot.email }}
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
                        <el-col :md="8" :sm="12">
                            <div class="fcal_spot_details_row">
                                <div class="fcal_spot_details_label">
                                    Status
                                </div>
                                <div class="fcal_spot_details_value">
                                    <span :class="'fcal_'+showing_spot.status">{{ showing_spot.status }}</span>
                                </div>
                            </div>
                        </el-col>
                        <el-col :md="8" :sm="12">
                            <div class="fcal_spot_details_row">
                                <div class="fcal_spot_details_label">
                                    Booked at
                                </div>
                                <div class="fcal_spot_details_value">
                                    <span>{{ toCurrentTimezone(showing_spot.created_at, 'DD MMM YYYY, hh:mma') }}</span>
                                </div>
                            </div>
                        </el-col>
                        <el-col :md="8" :sm="12">
                            <div v-if="showing_spot.phone" class="fcal_spot_details_row">
                                <div class="fcal_spot_details_label">
                                    Phone
                                </div>
                                <div class="fcal_spot_details_value">
                                    {{ showing_spot.phone }}
                                </div>
                            </div>
                        </el-col>
                    </el-row>
                </el-col>
            </el-row>
            <div v-if="showing_spot.message" class="fcal_spot_details_row">
                <div class="fcal_spot_details_label">
                    Comments by Invitee
                </div>
                <div class="fcal_spot_details_value">
                    {{ showing_spot.message || 'N/A' }}
                </div>
            </div>
            <editable-spot-data @dataUpdated="handleDataUpdated" :spot="showing_spot" data_key="internal_note"
                                input_type="textarea"
                                input_label="Internal Note"></editable-spot-data>

            <hr/>
            <h3 class="fcal_section_title">Meeting Activities</h3>

            <booking-activities :booking_id="showing_spot.id"/>

        </div>
        <el-skeleton v-if="fetching_spot"></el-skeleton>
        <el-dialog width="30%" title="Cancel Meeting" v-model="cancelDialog">
            <div style="text-align: center;">
                <h3>{{ showing_spot.slot.title }}</h3>
                <p>with <b>{{ showing_spot.first_name }} {{ showing_spot.last_name }}</b></p>
                <p>{{ toCurrentTimezone(showing_spot.start_time, 'MMMM D, YYYY hh:mma') }} -
                    {{ toCurrentTimezone(showing_spot.end_time, 'MMMM D, YYYY hh:mma') }}</p>

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
    watch: {
        spot_id() {
            this.showing_spot = null;
            this.fetching_spot = true;
            setTimeout(() => {
                this.showing_spot = this.spot;
                this.fetching_spot = false;
            }, 150);
        }
    },
    data() {
        return {
            updating: false,
            fetching_spot: false,
            showing_spot: this.spot,
            cancelDialog: false,
            cancel_reason: ''
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

            this.$put(`schedules/${this.showing_spot.id}`, data)
                .then(response => {
                    this.$notify.success(response.message);
                    this.showing_spot.status = new_status;
                    this.showing_spot.happening_status = '';

                    if (this.spot) {
                        this.spot.status = new_status;
                        this.spot.happening_status = '';
                    }

                    this.cancelDialog = false;
                })
                .catch((errors) => {
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
                    this.showing_spot = response.schedule;
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
