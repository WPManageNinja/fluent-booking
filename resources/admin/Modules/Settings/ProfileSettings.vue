<template>
    <div class="fcal_settings_body_inner fcal_settings_profile">
        <div class="fcal_settings_header">
            <h3>{{ $t('Profile') }}</h3>
        </div>

        <div class="fcal_settings_content_wrap">
            <el-form v-model="formData">
                <el-form-item>
                    <PhotoWidget
                        class="fcal_photo_changed"
                        btn_type="default"
                        btn_text="+"
                        :btn_mode="true"
                        @changed="updateAvatar"
                        v-model="profile.avatar"
                    />
                </el-form-item>
                <el-form-item>
                    <span class="input-label">
                        {{ $t('Your Name') }}
                        <el-tooltip
                            class="box-item"
                            effect="dark"
                            :content="$t('Enter Your name here')"
                            placement="top"
                        >
                        <span class="help">!</span>
                      </el-tooltip>
                    </span>
                    <el-input v-model="formData.name" placeholder="" />
                </el-form-item>
            </el-form>

            <div class="fcal_settings_footer">
                <el-button class="fcal_primary_btn">
                    {{ $t('Save Changes') }}
                </el-button>
            </div>
        </div>
    </div>
</template>

<script>
import PhotoWidget from "../../Pieces/PhotoWidget";

export default {
    name: 'ProfileSettings',
    components: {
        PhotoWidget
    },
    data() {
        return {
            formData: {
                name: '',
                photoURL: ''
            },
            profile: {
                avatar: ''
            }
        }
    },
    methods: {
        updateAvatar(url) {
            this.profile.avatar = url;
        },
    }
}
</script>

<style scoped>

</style>