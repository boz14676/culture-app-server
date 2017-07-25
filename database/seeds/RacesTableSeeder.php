<?php

use Illuminate\Database\Seeder;
use App\Models\v2\Race;

class RacesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 赛事数据
        $race_data = [
            
            [
                'race_category_id' => Race::CATEGORY_WEISAI,
                'name' => '2017URun清华校园马拉松',
                'logo' => 'race01_logo.png',
                'urun_logo' => 'race01_urun_logo.png', // new
                'sponsor' => '清华大学体育运动委员会', // new
                'banner' => 'qing_banner1.png,qing_banner2.png,qing_banner3.png',
                'location' => '清华大学紫荆操场',
                'count' => DB::table('race_1_sheet')->count(),
                'activity_time_start' => '2017-04-15 14:00:00',
                'activity_time_end' => '2017-04-15 17:00:00',
                'apply_time_start' => NULL,
                'apply_time_end' => NULL,
                'status' => Race::STATUS_UNACTION,
                'route_type' => Race::ROUTE_ROAD,
                'contact_phone' => '',
                'notice' => ''
            ],
            
            [
                'race_category_id' => Race::CATEGORY_NORMAL,
                'name' => 'U-Run2017北大站夜奔',
                'logo' => 'race02_logo.png',
                'urun_logo' => 'race02_urun_logo.png', // new
                'sponsor' => '北京大学体育教研部', // new
                'banner' => 'bei_banner1@2x.png,bei_banner2@2x.png,bei_banner3@2x.png,bei_banner4@2x.png,bei_banner5@2x.png',
                'location' => '北京大学校园',
                'count' => 2000,
                'activity_time_start' => '2017-05-20 18:00:00',
                'activity_time_end' => '2017-05-20 22:00:00',
                'apply_time_start' => '2017-05-10',
                'apply_time_end' => '2017-05-15 23:59:59',
                'status' => Race::STATUS_UNACTION,
                'route_type' => Race::ROUTE_ROAD,
                'contact_phone' => '010-84083859',
                'notice' => '<view class="detail-notice hide"> <view class="notice-warp"> <view class="notice-title">报名须知</view><view class="notice-item">(一)活动对象 </view> <view class="notice-item">凡北京大学各类在校学生、教职工等。亦可报名参加活动，具体名额另行规定。 </view> <view class="notice-item">(二)活动要求 </view> <view class="notice-item">活动参与者须身体健康（建议参加体检），并有经常参加体育跑步锻炼的习惯。孕妇及有下列疾病者不宜参加： </view> <view class="notice-item">1、先天性心脏病和风湿性心脏病患者；2、高血压和脑血管疾病患者；3、心肌炎和其他心脏病患者；4、冠状动脉病患者和严重心律不齐者；5、血糖过高或过低的糖尿病患者；6、其它不适合运动的疾病患者。 </view> <view class="notice-item">(三)报名办法 </view> <view class="notice-item">1、报名时间：2017年5月4日09:00- 5月15日24:00（2017年5月4日报名网站开放用户注册） </view> <view class="notice-item">2、报名办法： </view> <view class="notice-item">活动参与人员需登录(http://infordata.cn主页链接)或微信客户端（微信搜索公众号“友跑”加关注后进入报名页面）进行手机注册报名。须填写所有个人实名信息进行身份注册（组委会将为每位人员购买保险，请务必填写真实信息以使保单有效）。报名信息经审核通过视为报名成功，短信通知或登录后查询审核情况。 </view> <view class="notice-item">3、报名人数限制： </view> <view class="notice-item">U-run2017北京大学校园健身盛典-北大夜奔：学生自愿报名，男女不限，报名按先到先得原则，报名额满即止。 </view> <view class="notice-item">4、活动包领取办法： </view> <view class="notice-item">活动组委会将为参加U-trun的人员提供活动装备，包括活动参赛包、活动T恤、发光手环、纪念品、活动指南等。活动参与人员于5月17日，持本人身份证前往北京大学三角地现场确认并领取活动包。网上恶意抢注报名或报名后领取活动包而无故不活动是不道德行为，既影响了其他活动选手报名，同时也给组织工作带来困难，将被严格禁止，主办方对以上行为保留追缴活动包及进一步处分的权力。 </view> <view class="notice-item">(四)活动声明 </view> <view class="notice-item">所有活动人员报名之前必须认真阅读活动规程。活动人员提交报名信息即被默认为同意此活动规程上的一切内容并做出以下声明： </view> <view class="notice-item">1、本人自愿报名参加U-run 2017北京大学校园健身盛典-北大夜奔；2、本人全面理解并同意遵守组委会及主办单位所制订的各项活动规程、规则、规定、要求及采取的措施；3、本人身心健康，已为活动做好充分准备；4、本人全面理解活动可能出现的风险，已准备必要的防范措施，愿意承担活动期间发生的自身意外风险责任，且同意主办单位等活动机构作出的对于非承办单位原因造成的伤害、死亡或其他一切损失不承担任何形式的赔偿之责任；5、本人同意接受主办单位在活动期间提供的现场急救性质的医务治疗，但在医院救治等发生的相关费用由活动人员自理；6、本人承诺以自己的名义报名并参加活动，绝不将报名后获得的物品以任何方式转让给他人，否则产生的后果完全由本人负责；7、本人保证向组委会提供有效的身份证件和资料用于核实身份，并同意承担因身份证件和资料不实所产生的全部责任；8、本人已认真阅读并全面理解以上的内容，且对上述所有内容予以确认并承担相应的法律责任。 </view> </view> </view>'
            ],
    
            [
                'race_category_id' => Race::CATEGORY_NORMAL,
                'name' => 'U-Run2017北大站Training',
                'logo' => 'race02_logo.png',
                'urun_logo' => 'race02_urun_logo.png', // new
                'sponsor' => '北京大学体育教研部', // new
                'banner' => 'bei_banner1@2x.png,bei_banner2@2x.png,bei_banner3@2x.png,bei_banner4@2x.png,bei_banner5@2x.png',
                'location' => '北京大学五四操场',
                'count' => 1000,
                'activity_time_start' => '2017-05-19 18:00:00',
                'activity_time_end' => '2017-05-19 22:00:00',
                'apply_time_start' => '2017-05-10',
                'apply_time_end' => '2017-05-15 23:59:59',
                'status' => Race::STATUS_UNACTION,
                'route_type' => Race::ROUTE_PLAYGROUND,
                'contact_phone' => '010-84083859',
                'notice' => '<view class="detail-notice hide"> <view class="notice-warp"> <view class="notice-title">报名须知</view><view class="notice-item">(一)活动对象 </view> <view class="notice-item">凡北京大学各类在校学生、教职工等。亦可报名参加活动，具体名额另行规定。 </view> <view class="notice-item">(二)活动要求 </view> <view class="notice-item">活动参与者须身体健康（建议参加体检），并有经常参加体育跑步锻炼的习惯。孕妇及有下列疾病者不宜参加： </view> <view class="notice-item">1、先天性心脏病和风湿性心脏病患者；2、高血压和脑血管疾病患者；3、心肌炎和其他心脏病患者；4、冠状动脉病患者和严重心律不齐者；5、血糖过高或过低的糖尿病患者；6、其它不适合运动的疾病患者。 </view> <view class="notice-item">(三)报名办法 </view> <view class="notice-item">1、报名时间：2017年5月4日09:00- 5月15日24:00（2017年5月4日报名网站开放用户注册） </view> <view class="notice-item">2、报名办法： </view> <view class="notice-item">活动参与人员需登录(http://infordata.cn主页链接)或微信客户端（微信搜索公众号“友跑”加关注后进入报名页面）进行手机注册报名。须填写所有个人实名信息进行身份注册（组委会将为每位人员购买保险，请务必填写真实信息以使保单有效）。报名信息经审核通过视为报名成功，短信通知或登录后查询审核情况。 </view> <view class="notice-item">3、报名人数限制： </view> <view class="notice-item">U-run2017北京大学校园健身盛典-北大夜奔：学生自愿报名，男女不限，报名按先到先得原则，报名额满即止。 </view> <view class="notice-item">4、活动包领取办法： </view> <view class="notice-item">活动组委会将为参加U-trun的人员提供活动装备，包括活动参赛包、活动T恤、发光手环、纪念品、活动指南等。活动参与人员于5月17日，持本人身份证前往北京大学三角地现场确认并领取活动包。网上恶意抢注报名或报名后领取活动包而无故不活动是不道德行为，既影响了其他活动选手报名，同时也给组织工作带来困难，将被严格禁止，主办方对以上行为保留追缴活动包及进一步处分的权力。 </view> <view class="notice-item">(四)活动声明 </view> <view class="notice-item">所有活动人员报名之前必须认真阅读活动规程。活动人员提交报名信息即被默认为同意此活动规程上的一切内容并做出以下声明： </view> <view class="notice-item">1、本人自愿报名参加U-run 2017北京大学校园健身盛典-北大夜奔；2、本人全面理解并同意遵守组委会及主办单位所制订的各项活动规程、规则、规定、要求及采取的措施；3、本人身心健康，已为活动做好充分准备；4、本人全面理解活动可能出现的风险，已准备必要的防范措施，愿意承担活动期间发生的自身意外风险责任，且同意主办单位等活动机构作出的对于非承办单位原因造成的伤害、死亡或其他一切损失不承担任何形式的赔偿之责任；5、本人同意接受主办单位在活动期间提供的现场急救性质的医务治疗，但在医院救治等发生的相关费用由活动人员自理；6、本人承诺以自己的名义报名并参加活动，绝不将报名后获得的物品以任何方式转让给他人，否则产生的后果完全由本人负责；7、本人保证向组委会提供有效的身份证件和资料用于核实身份，并同意承担因身份证件和资料不实所产生的全部责任；8、本人已认真阅读并全面理解以上的内容，且对上述所有内容予以确认并承担相应的法律责任。 </view> </view> </view>'
            ],
            
        ];
        db::table('races')->insert($race_data);
    }
}