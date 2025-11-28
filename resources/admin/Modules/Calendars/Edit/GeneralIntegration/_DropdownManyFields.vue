<template>
    <div class="dropdown_label_repeater">
        <table class="fcal_table">
            <thead>
            <tr>
                <th>{{ $t(rendered_labels.remote_text) }}</th>
                <th>{{ $t(rendered_labels.local_text) }}</th>
                <th style="width: 100px;"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(item, itemIndex) in settings[field.key]" :key="'item_'+itemIndex">
                <td>
                    <el-select class="w-100" popper-class="fcal_select" :placeholder="$t('Select')" v-model="item.label">
                        <el-option
                            v-for="(optionLabel, optionValue) in field.options"
                            :key="optionValue"
                            :label="optionLabel"
                            :value="optionValue"
                        ></el-option>
                    </el-select>
                </td>
                <td>
                    <field-general
                        :editorShortcodes="editorShortcodes"
                        v-model="item.item_value"
                    ></field-general>
                </td>
                <td>
                    <action-btn>
                        <action-btn-add @clicked="addItemAfter(itemIndex)"></action-btn-add>
                        <action-btn-remove v-if="settings[field.key].length > 1" @click="removeItem(itemIndex)"></action-btn-remove>
                    </action-btn>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script type="text/babel">
    import FieldGeneral from './_FieldGeneral.vue';
    import ActionBtn from '@/Components/ActionBtn/ActionBtn.vue';
    import ActionBtnAdd from '@/Components/ActionBtn/ActionBtnAdd.vue';
    import ActionBtnRemove from '@/Components/ActionBtn/ActionBtnRemove.vue';

    export default {
        name: 'dropdown_many_fields',
        props: [
            'settings',
            'field',
            'inputs',
            'errors',
            'editorShortcodes'
        ],
        data() {
            return {
                rendered_labels: {
                    remote_text: this.field.remote_text || 'Field Label',
                    local_text: this.field.local_text || 'Field Value'
                }
            }
        },
        components: {
            FieldGeneral,
            ActionBtn,
            ActionBtnAdd,
            ActionBtnRemove
        },
        methods: {
            addItemAfter(index) {
                this.settings[this.field.key].splice( index + 1, 0, {
                    item_value: '',
                    label: ''
                } );
            },
            removeItem(index) {
                this.settings[this.field.key].splice(index, 1);
            }
        },
        mounted() {
            if(!this.settings[this.field.key] || !this.settings[this.field.key].length) {
                this.settings[this.field.key] = [
                    {
                        item_value: this.field,
                        label: ''
                    }
                ]
            }
        }
    }
</script>
