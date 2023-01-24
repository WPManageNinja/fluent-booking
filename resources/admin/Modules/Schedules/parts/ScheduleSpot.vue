<template>
    <div :class="'fcal_spoot_status_' + spot.status" class="fcal_spot_wrapper">
        <div @click="showDetails = !showDetails" class="fcal_spot_line">
            <div class="fcal_spot_color">
                <span :class="'fcal_'+spot.status"></span>
            </div>
            <div class="fcal_spot_timing">
                {{ toCurrentTimezone(spot.start_time, 'HH:mma') }} - {{ toCurrentTimezone(spot.end_time, 'HH:mma') }}
                <div v-if="spot.happening_status" class="fcal_spot_happening">
                    <span :class="'fcal_'+spot.happening_status">{{ getTextFromSlug(spot.happening_status) }}</span>
                </div>
            </div>
            <div class="fcal_spot_desc">
                <div class="fcal_spot_title">
                    {{ spot.first_name }} {{ spot.last_name }}
                </div>
                <div class="fcal_spot_desc_text">
                    Event: <b>{{ spot.slot.title }}</b>
                </div>
            </div>
            <div class="fcal_spot_actions">
                <el-button text>Details</el-button>
            </div>
        </div>
        <div v-if="showDetails" class="fcal_spot_details">
            <el-row :gutter="30">
                <el-col :md="18" :sm="24">
                    <el-row :gutter="30">
                        <el-col :md="8" :sm="12">
                            <div class="fcal_spot_details_row">
                                <div class="fcal_spot_details_label">
                                    Email
                                </div>
                                <div class="fcal_spot_details_value">
                                    {{ spot.email }}
                                </div>
                            </div>
                        </el-col>
                        <el-col :md="8" :sm="12">
                            <div class="fcal_spot_details_row">
                                <div class="fcal_spot_details_label">
                                    Invitee Time Zone
                                </div>
                                <div class="fcal_spot_details_value">
                                    {{ spot.person_time_zone }}
                                </div>
                            </div>
                        </el-col>
                        <el-col :md="8" :sm="12">
                            <div class="fcal_spot_details_row">
                                <div class="fcal_spot_details_label">
                                    Status
                                </div>
                                <div class="fcal_spot_details_value">
                                    <span :class="'fcal_'+spot.status">{{ spot.status }}</span>
                                </div>
                            </div>
                        </el-col>
                        <el-col :md="8" :sm="12">
                            <div class="fcal_spot_details_row">
                                <div class="fcal_spot_details_label">
                                    Booked at
                                </div>
                                <div class="fcal_spot_details_value">
                                    <span>{{ toCurrentTimezone(spot.created_at, 'DD MMM YYYY, HH:mma') }}</span>
                                </div>
                            </div>
                        </el-col>
                        <el-col :md="8" :sm="12">
                            <div v-if="spot.phone" class="fcal_spot_details_row">
                                <div class="fcal_spot_details_label">
                                    Phone
                                </div>
                                <div class="fcal_spot_details_value">
                                    {{ spot.phone }}
                                </div>
                            </div>
                        </el-col>
                    </el-row>
                </el-col>
                <el-col v-if="spot.status != 'cancelled'" :md="6" :sm="24">
                    <div class="fcal_spot_actions">

                        <confirm v-if="spot.status == 'scheduled'" placement="top-start" @yes="updateSpotStatus('cancelled')">
                            <template #reference>
                                <el-button
                                    v-loading="updating"
                                    :disabled="updating"
                                    type="danger">
                                    Cancel Booking
                                </el-button>
                            </template>
                        </confirm>

                        <confirm v-if="(spot.status == 'completed' || spot.happening_status) && spot.status != 'no_show'" placement="top-start" @yes="updateSpotStatus('no_show')">
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
                </el-col>
            </el-row>
            <div v-if="spot.message" class="fcal_spot_details_row">
                <div class="fcal_spot_details_label">
                    Comments by Invitee
                </div>
                <div class="fcal_spot_details_value">
                    {{ spot.message || 'N/A' }}
                </div>
            </div>
            <editable-spot-data :spot="spot" data_key="internal_note" input_type="textarea"
                                input_label="Internal Note"></editable-spot-data>
        </div>
    </div>
</template>

<script type="text/babel">
import EditableSpotData from "./EditableSpotData.vue";
import Confirm  from "../../../Pieces/Confirm.vue";
export default {
    name: 'ScheduleSpot',
    props: ['spot'],
    components: {
        Confirm,
        EditableSpotData
    },
    data() {
        return {
            showDetails: false,
            updating: false
        }
    },
    methods: {
        updateSpotStatus(new_status) {
            this.updating = true;
            this.$put(`schedules/spot/${this.spot.id}`, {
                column: 'status',
                value: new_status
            })
                .then(response => {
                    this.$notify.success(response.message);
                    this.spot.status = new_status;
                    this.spot.happening_status = '';
                    this.showDetails = false;
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.updating = false;
                });
        }
    }
}
</script>
