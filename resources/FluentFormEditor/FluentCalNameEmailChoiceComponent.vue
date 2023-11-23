<template>
    <div class="fcal_input_groups">
        <el-form-item :label="$t('Select Guest Email Field')">
            <el-select style="width: 100%;" v-model="editItem.settings.cal_guest_fields.email_field" :placeholder="$t('Select Guest Email Field')"
                       :no-match-text="$t('No Data match')"
                       :no-data-text="$t('No Data')">
                <el-option
                    v-for="(item, itemName) in dependencies"
                    :key="itemName"
                    :label="item"
                    :value="itemName">
                </el-option>
            </el-select>
        </el-form-item>
        <el-form-item :label="$t('Select Guest Name Field')">
            <el-select style="width: 100%;" v-model="editItem.settings.cal_guest_fields.name_field" :placeholder="$t('Select Guest Name Field')"
                       :no-match-text="$t('No Data match')"
                       :no-data-text="$t('No Data')">
                <el-option
                    v-for="(item, itemName) in dependencies"
                    :key="itemName"
                    :label="item"
                    :value="itemName">
                </el-option>
            </el-select>
        </el-form-item>
        <el-form-item>
            <el-checkbox v-model="editItem.settings.cal_guest_fields.host_info" :true-label="'show'" :false-label="'hide'">{{ $t('Show Host Info') }}</el-checkbox>
        </el-form-item>
    </div>
</template>
<script type="text/babel">
export default {
    name: 'FluentCalNameEmailChoiceComponent',
    props: ['listItem', 'editItem', 'form_items'],
    computed: {
        dependencies() {
            const acceptedElements = ['input_text', 'input_name', 'input_email', 'input_hidden'];
            let dependencies = {};
            this.mapElements(this.form_items, (formItem) => {
                if(acceptedElements.indexOf(formItem.element) == -1) {
                    return;
                }
                dependencies[formItem.attributes.name] = formItem.settings.admin_field_label || formItem.settings.label || formItem.attributes.name;
            });

            return dependencies;
        }
    },
    data() {
        return {
            appReady: false,
        }
    },
    methods: {
        mapElements(allElements, callback) {
            _ff.map(allElements, (existingItem) => {
                if (existingItem.element != 'container') {
                    callback(existingItem);
                }
                if (existingItem.element == 'container') {
                    _ff.map(existingItem.columns, (column) => {
                        this.mapElements(column.fields, callback);
                    });
                }
            });
        }
    },
    mounted() {
        if(!this.editItem?.settings?.cal_guest_fields) {
            this.editItem.settings.cal_guest_fields = {
                email_field: '',
                name_field: '',
                host_info: 'hide'
            };
        }
        this.$nextTick(() => {
            this.appReady = true;
        });
    }
}
</script>
