<template>
    <div class="fcal_spot_details">
        <div v-if="showing_spot">
            <el-row :gutter="30">
                <el-col :md="18" :sm="24">
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
                                    <span>{{ toCurrentTimezone(showing_spot.created_at, 'DD MMM YYYY, HH:mma') }}</span>
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
                <el-col v-if="showing_spot.status != 'cancelled'" :md="6" :sm="24">
                    <div class="fcal_item_actions">
                        <confirm v-if="showing_spot.status == 'scheduled'" placement="top-start" @yes="updateSpotStatus('cancelled')">
                            <template #reference>
                                <el-button
                                    v-loading="updating"
                                    :disabled="updating"
                                    type="danger">
                                    Cancel Booking
                                </el-button>
                            </template>
                        </confirm>

                        <confirm message="Are you sure you want to change the status?" v-if="(showing_spot.status == 'completed' || showing_spot.happening_status) && showing_spot.status != 'no_show'" placement="top-start" @yes="updateSpotStatus('no_show')">
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
            <div v-if="showing_spot.message" class="fcal_spot_details_row">
                <div class="fcal_spot_details_label">
                    Comments by Invitee
                </div>
                <div class="fcal_spot_details_value">
                    {{ showing_spot.message || 'N/A' }}
                </div>
            </div>
            <editable-spot-data @dataUpdated="handleDataUpdated" :spot="showing_spot" data_key="internal_note" input_type="textarea"
                                input_label="Internal Note"></editable-spot-data>
        </div>
        <el-skeleton v-if="fetching_spot"></el-skeleton>
    </div>
</template>

<script type="text/babel">
import EditableSpotData from "./EditableSpotData.vue";
import Confirm  from "../../../Pieces/Confirm.vue";
export default {
    name: 'SpotInfo',
    props: ['spot_id', 'spot'],
    $emits: ['spotFetched'],
    components: {
        EditableSpotData,
        Confirm
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
            showing_spot: this.spot
        }
    },
    methods: {
        updateSpotStatus(new_status) {
            this.updating = true;
            this.$put(`schedules/spot/${this.showing_spot.id}`, {
                column: 'status',
                value: new_status
            })
                .then(response => {
                    this.$notify.success(response.message);
                    this.showing_spot.status = new_status;
                    this.showing_spot.happening_status = '';

                    if(this.spot) {
                        this.spot.status = new_status;
                        this.spot.happening_status = '';
                    }
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.updating = false;
                });
        },
        fetchSpot() {
            this.fetching_spot = true;
            this.$get(`schedules/spot/${this.spot_id}`)
                .then(response => {
                    this.showing_spot = response.spot;
                    this.$emit('spotFetched', response.spot);
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.fetching_spot = false;
                });
        },
        handleDataUpdated(data) {
            if(this.spot) {
                this.spot[data.key] = data.value;
            }
        }
    },
    mounted() {
        if(!this.spot) {
            this.fetchSpot();
        }
    }
}
</script>
