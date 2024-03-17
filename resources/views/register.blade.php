@include('includes.head')
<div id="content">
    <el-form :model="form">
        <el-form-item label="用戶圖片">
            <el-upload
                action="#"
                class="upload-demo"
                :headers="{ 'X-CSRF-TOKEN': csrfToken , 'Content-Type': 'multipart/form-data'}"
                v-model:file-list="form.user_picture"
                :auto-upload="false"
                list-type="picture"
                limit="1"
            >
                <el-button type="primary">上傳</el-button>
                <template #tip>
                    <div class="el-upload__tip">
                        jpg/png files with a size less than 500KB.
                    </div>
                </template>
            </el-upload>
        </el-form-item>
        <el-form-item  width="100" label="姓名">
                <el-col :span="12">
                    <el-input v-model="form.first_name" label="First Name" placeholder="First Name" />
                </el-col>
                <el-col :span="12">
                    <el-input v-model="form.last_name" label="Last Name" placeholder="Last Name" />
                </el-col>
        </el-form-item>
        <el-form-item label="email">
            <el-input v-model="form.email">
        </el-form-item>
        <el-form-item label="生日">
            <el-date-picker
                style="width: 100%;"
                v-model="form.birthdate"
                type="date"
                placeholder="選擇日期"
                format="YYYY-MM-DD"
                value-format="YYYY-MM-DD"
            >
        </el-form-item>
        <el-form-item label="密碼">
            <el-input type="password" v-model="form.password">
        </el-form-item>
        <el-form-item label="Address Type">
            <el-select v-model="form.address_type_id" placeholder="Address_Type">
                <el-option
                    v-for="item in address_types"
                    :key="item.id"
                    :label="item.name"
                    :value="item.id"
                >
                </el-option>
            </el-select>
        </el-form-item>
        <el-form-item label="地址">
            <el-col :span="2">
                <el-input v-model="form.zipcode" label="ZipCode" placeholder="ZipCode" :disabled="true" />
            </el-col>
            <el-col :span="3">
                <el-select v-model="form.city" @change="getAreaList" placeholder="City">
                    <el-option
                        v-for="item in city"
                        :key="item.id"
                        :label="item.name"
                        :value="item.name"
                    >
                    </el-option>
                </el-select>
            </el-col>
            <el-col :span="3">
                <el-select v-model="form.country" placeholder="Area" :disabled="form.city == undefined" @change="getZipcode">
                    <el-option
                        v-for="item in area"
                        :key="item.id"
                        :label="item.name"
                        :value="item.name"
                    >
                    </el-option>
                </el-select>
            </el-col>
            <el-col :span="16">
                <el-input v-model="form.address" label="Address" placeholder="Address" />
            </el-col>
        </el-form-item>
        <el-form-item label="地址證明文件">
            <el-upload
                action="#"
                class="upload-demo"
                :headers="{ 'X-CSRF-TOKEN': csrfToken , 'Content-Type': 'multipart/form-data'}"
                v-model:file-list="form.proof"
                :auto-upload="false"
                list-type="picture"
                limit="1"
            >
                <el-button type="primary">上傳</el-button>
                <template #tip>
                    <!-- <div class="el-upload__tip">
                        jpg/png files with a size less than 500KB.
                    </div> -->
                </template>
            </el-upload>
        </el-form-item>
        <el-form-item>
            <el-button type="primary" @click="handlePrev">上一頁</el-button><el-button type="primary" @click="send">送出</el-button>
        </el-form-item>
    </el-form>
</div>
<script>
    const { ElMessage } = ElementPlus;
    const id = "{{ $id??false }}";
    createApp({
        setup() {
            const form = ref({
                user_picture:'',
                first_name:'',
                last_name:'',
                email:'',
                birthdate:'',
                password:'',
                address_type:'',
                zipcode:'',
                city:'',
                country:'',
                address:'',
                proof:''
            });
            const city = ref();
            const area = ref();
            const address_types = ref();

            const send = () => {
                const a = Object.keys(form.value).filter((row) => {
                    if(form.value[row] == ''){
                        return row;
                    }
                })
                // if(a.length > 0){
                //     ElMessage.error('請確認資料是否填寫完成');
                //     return false;
                // }
                validateFile();
            }

            const proof = ref();
            const user_picture = ref();

            const validateFile = () => {
                const data = new FormData();
                if(form.value.proof[0].raw != ''){
                    data.append('proof', form.value.proof[0].raw, form.value.proof[0].name);
                }
                if(form.value.user_picture[0].raw != ''){
                    data.append('user_picture', form.value.user_picture[0].raw, form.value.user_picture[0].name);
                }
                if(form.value.user_picture[0].raw != '' || form.value.proof[0].raw != ''){
                    axios.post('/validateFile', data, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    })
                    .then((res) => {
                        if(res.data.data.proof != false){
                            proof.value = res.data.data.proof;
                        }
                        if(res.data.data.user_picture != false){
                            user_picture.value = res.data.data.user_picture;
                        }
                        saveForm();
                    })
                    .catch((error) => {
                        errorMessage(error.response.data);
                        return false;
                    })
                }else{
                    saveForm();
                }
            }

            const errorMessage = (error) => {
                ElMessage.error('請檢查資料格式、類型');
            }

            const saveForm = () => {
                const formData = {
                    users:{
                        first_name:form.value.first_name,
                        last_name:form.value.last_name,
                        email:form.value.email,
                        birthdate:form.value.birthdate,
                        password:form.value.password,
                    },
                    address:{
                        address_type_id:form.value.address_type_id,
                        zipcode:form.value.zipcode,
                        city:form.value.city,
                        country:form.value.country,
                        address:form.value.address,
                    },
                    documents:{
                        user_picture:user_picture.value,
                        proof:proof.value,
                    },
                    id: {
                        id: id
                    }
                };
                const uri = id != false?'/updateForm':'/saveForm';
                axios.post(uri, formData)
                .then((res) => {
                    window.location.href="/index";
                })
                .catch((error) => {
                    ElMessage.error(error.response.data.errors[0]);
                    return false;
                })
            }

            const getCityList = async() => {
                axios.get('/getCityList')
                .then((res) => {
                    city.value = res.data;
                })
            }

            const getAreaList = () => {
                form.value.country = '';
                form.value.zipcode = '';
                data = city.value.filter(row => row.name == form.value.city);
                axios.post('/getAreaList', {id: data[0].id})
                .then((res) => {
                    area.value = res.data;
                })
            }

            const getZipcode = () => {
                data = area.value.filter(row => row.name == form.value.country);
                form.value.zipcode = data[0].zipcode;
            }

            const getAddressType = () => {
                axios.get('/getAddressType')
                .then((res) => {
                    address_types.value = res.data;
                })
            }

            const getDetail = () => {
                axios.post('/getDetail', {id:id})
                .then((res) => {
                    form.value = res.data;
                    form.value.user_picture = [
                        {name:form.value.user_picture.split('/uploads/')[1], raw:'', url:form.value.user_picture}
                    ];
                    user_picture.value = form.value.user_picture[0].name;
                    form.value.proof = [
                        {name:form.value.proof.split('/uploads/')[1], raw:'', url:form.value.proof}
                    ];
                    proof.value = form.value.proof[0].name;
                    console.log(proof.value);
                })
            }

            const handlePrev = () => {
                window.history.back();
            }

            onMounted(() => {
                getCityList();
                getAddressType();
                if(id){
                    getDetail();
                }
            });
            return {
                form,
                send,
                city,
                getAreaList,
                area,
                getZipcode,
                getAddressType,
                address_types,
                handlePrev,
            }
        }
    }).use(ElementPlus).mount('#content')
</script>