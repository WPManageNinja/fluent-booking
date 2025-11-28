<template>
    <el-dialog
        v-model="showModal"
        :append-to-body="true"
        class="fcal_dialog fcal_reorder_event_dialog">
        <template #header>
            <h3>{{ $t('Reorder Events') }}</h3>
        </template>
        <div class="fcal_reorder_wrapper">
            <div class="fcal_reorder_header">
                <img class="fcal_author_avatar" :src="calendar.author_profile.avatar">
                <h2 class="fcal_author_title"> {{ calendar.title }} </h2>
            </div>
            <div v-if="calendarEvents.length" class="fcal_reorder_events">
                <div v-for="event in orderedCalendarEvents" :key="event.id" class="fcal_reorder_event">
                    <div class="fcal_reorder_icon">
                        <el-icon @click="moveUp(event.id)"><Top /></el-icon>
                        <el-icon @click="moveDown(event.id)"><Bottom /></el-icon>
                    </div>
                    <div class="fcal_event_card">
                        <h3 class="fcal_event_title">
                            <span class="fcal_event_color" :style="`background: ${event.color_schema}`"></span>
                            {{ event.title }}
                        </h3>
                        <p class="fcal_event_description">
                            {{ event.short_description }}
                        </p>
                        <div class="fcal_event_infos">
                            <span class="fcal_event_info">
                                <el-icon><Clock/></el-icon> {{ getDuration(event) }} 
                            </span>
                            <span class="fcal_event_info">
                                <el-icon><Location/></el-icon> {{ event.location_settings[0].title }}
                            </span>
                            <span v-if="event.price_total" class="fcal_event_info">
                                <el-icon><CreditCard/></el-icon> {{ $currencyFormat(event.price_total) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="fcal_no_event">{{ $t('No events found') }}</div>
        </div>
        <template #footer>
            <div class="dialog-footer">
                <el-button class="fcal_plain_btn" @click="showModal = false">
                    {{ $t('Close') }}
                </el-button>
                <el-button class="fcal_primary_btn" :disabled="saving" v-loading="saving" @click="saveEventOrder">
                    {{ $t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script>
import { Top, Bottom, Clock, Location, CreditCard } from '@element-plus/icons-vue';
export default {
    name: 'ReorderDialog',
    props: ['calendar', 'openModal'],
    emits: ['closeModal', 'saveOrder'],
    components: {
        Top,
        Bottom,
        Clock,
        Location,
        CreditCard
    },
    data() {
        return {
            saving: false,
            showModal: this.openModal,
            calendarEvents: this.calendar?.slots ?? [],
            eventOrder: this.calendar?.event_order ?? [],
            durations: this.appVars.duration_lookup,
            multiDurations: this.appVars.multi_duration_lookup
        }
    },
    watch: {
        showModal() {
            this.$emit('closeModal');
        }
    },
    computed: {
        getDuration() {
            return (event) => {
                const duration = event.duration;
                const isMultiDurationEnabled = event.settings?.multi_duration?.enabled;
                const durationSource = isMultiDurationEnabled ? this.multiDurations : this.durations;
                return durationSource[duration] || `${duration} ${this.$t('Minutes')}`;
            }
        },
        orderedCalendarEvents() {
            return this.calendarEvents.slice().sort((a, b) => {
                const indexA = this.eventOrder.indexOf(a.id);
                const indexB = this.eventOrder.indexOf(b.id);
                const posA = indexA == -1 ? Number.MAX_SAFE_INTEGER : indexA;
                const posB = indexB == -1 ? Number.MAX_SAFE_INTEGER : indexB;
                return posA - posB;
            });
        }
    },
    methods: {
        moveUp(eventId) {
            const orders = this.eventOrder;
            const indx = orders.indexOf(eventId);
            if (indx > 0) {
                [orders[indx], orders[indx - 1]] = [orders[indx - 1], orders[indx]];
            }
            this.eventOrder = orders;
        },
        moveDown(eventId) {
            const orders = this.eventOrder;
            const indx = orders.indexOf(eventId);
            if (indx !== -1 && indx < orders.length - 1) {
                [orders[indx], orders[indx + 1]] = [orders[indx + 1], orders[indx]];
            }
            this.eventOrder = orders;
        },
        updateEventOrder() {
            this.calendarEvents.forEach(event => {
                if (!this.eventOrder.includes(event.id)) {
                    this.eventOrder.push(event.id)
                }
            })
        },
        saveEventOrder() {
            this.saving = true;
            this.$post('calendars/' + this.calendar.id + '/event-order', {
                event_order: this.eventOrder,
                calendar_id: this.calendar.id
            })
                .then(response => {
                    this.showModal = false;
                    this.$handleSuccess(response.message);
                    if (!this.calendar.event_order?.length) {
                        this.$emit('saveOrder');
                    }
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
        this.updateEventOrder();
    }
}
</script>
