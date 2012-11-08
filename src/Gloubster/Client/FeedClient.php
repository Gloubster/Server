<?php

namespace Gloubster\Client;

use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FeedClient extends Command
{

    public function __contruct($name = null)
    {
        parent::__construct($name);
        $this->setDescription('Feed client with various datas');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require __DIR__ . '/../App.php';

        $dm = $app['dm'];

        $images = array(

            "https://beta.alchemyasp.com/permalink/v1/OT-chateau-de-Ferrettetif/1/1104/8D7hEbmD/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7768jpg/1/1103/9Qk9wGDq/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7767JPG/1/1102/kXicDJrb/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7905jpg/1/1099/9SiHypBZ/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7766JPG/1/1095/jTYP3g6B/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7902jpg/1/1094/G56HQpCf/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7898JPG/1/1093/hpLuIqxb/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7765jpg/1/1092/bCdq0EW2/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7897JPG/1/1091/BBqYSH1W/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7912JPG/1/1090/O6u6vqeP/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7880JPG/1/1089/kpsMnPCW/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7874JPG/1/1088/gdPHuIXk/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7875JPG/1/1087/wqkHzBkg/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7877JPG/1/1086/Ctqe0gKb/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7878JPG/1/1085/8RtCHoET/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7879JPG/1/1084/4iHQJUv7/document/",
"https://beta.alchemyasp.com/permalink/v1/005Foretsjpg/1/1083/pRr1Ba28/document/",
"https://beta.alchemyasp.com/permalink/v1/004Foretsjpg/1/1082/gRlyo0yj/document/",
"https://beta.alchemyasp.com/permalink/v1/003Foretsjpg/1/1081/ny343MA5/document/",
"https://beta.alchemyasp.com/permalink/v1/002Foretsjpg/1/1080/0J46oawL/document/",
"https://beta.alchemyasp.com/permalink/v1/001Foretsjpg/1/1079/SQWfayjY/document/",
"https://beta.alchemyasp.com/permalink/v1/Live-Streamingjpeg/1/1078/WbuyCWO3/document/",
"https://beta.alchemyasp.com/permalink/v1/20120606_095237jpg/1/1077/LcBsMJG5/document/",
"https://beta.alchemyasp.com/permalink/v1/B3J8584CR2/1/1039/brdFl8wH/document/",
"https://beta.alchemyasp.com/permalink/v1/2010-08-15_0073RAF/1/1038/sInxLg48/document/",
"https://beta.alchemyasp.com/permalink/v1/_DSC5608NEF/1/1037/AOWq6J1T/document/",
"https://beta.alchemyasp.com/permalink/v1/Isuzu_84ai/1/1034/csyM9gwv/document/",
"https://beta.alchemyasp.com/permalink/v1/test001CR2/1/1032/IBkOgMxf/document/",
"https://beta.alchemyasp.com/permalink/v1/Lock-Out1jpg/1/1030/tUHDrB9N/document/",
"https://beta.alchemyasp.com/permalink/v1/lock-out-002-10679384edglc_1798jpg/1/1029/pY2FlNQh/document/",
"https://beta.alchemyasp.com/permalink/v1/Lock-Out-PHOTO-3jpg/1/1028/22OFyDVR/document/",
"https://beta.alchemyasp.com/permalink/v1/lock-out-2jpg/1/1027/iD0mn56M/document/",
"https://beta.alchemyasp.com/permalink/v1/lock_outjpg/1/1026/CA0V3fH8/document/",
"https://beta.alchemyasp.com/permalink/v1/Hiverjpg/1/1025/nwpF5BF7/document/",
"https://beta.alchemyasp.com/permalink/v1/Coucher-de-soleiljpg/1/1024/o5Lgc2Do/document/",
"https://beta.alchemyasp.com/permalink/v1/Collinesjpg/1/1023/dAW8Oy79/document/",
"https://beta.alchemyasp.com/permalink/v1/Nenupharsjpg/1/1022/82ij8z4C/document/",
"https://beta.alchemyasp.com/permalink/v1/523529_10150598173060910_333858023_njpg/1/1020/Li0EuofW/document/",
"https://beta.alchemyasp.com/permalink/v1/398786_10150598174555910_256117636_njpg/1/1019/8j5ZVw3k/document/",
"https://beta.alchemyasp.com/permalink/v1/318180_10150598174760910_80563933_njpg/1/1018/4DnHEIKc/document/",
"https://beta.alchemyasp.com/permalink/v1/1jpg/1/1017/eNNnLb2z/document/",
"https://beta.alchemyasp.com/permalink/v1/pnggrad8rgbpng/1/1013/MTWzYLlz/document/",
"https://beta.alchemyasp.com/permalink/v1/logo_seul_noir_vectoriseeps/1/1011/ukiPHHOR/document/",
"https://beta.alchemyasp.com/permalink/v1/annual_temperature_samplegif/1/1009/qkOz6VV9/document/",
"https://beta.alchemyasp.com/permalink/v1/2105137EPS/1/1008/JGJyFaUQ/document/",
"https://beta.alchemyasp.com/permalink/v1/_JCF0017tif/1/1007/HQiiaE0V/document/",
"https://beta.alchemyasp.com/permalink/v1/DSCF0035RAF/1/1000/NvmGbgww/document/",
"https://beta.alchemyasp.com/permalink/v1/laser-beams-4244143jpg/1/999/jEyT1TA6/document/",
"https://beta.alchemyasp.com/permalink/v1/376_40150900909_4922_njpg/1/998/ZVF3HyxG/document/",
"https://beta.alchemyasp.com/permalink/v1/376_40150870909_3730_njpg/1/997/HVJp1Ql5/document/",
"https://beta.alchemyasp.com/permalink/v1/376_40150025909_1785_njpg/1/996/2Q4Ae7dW/document/",
"https://beta.alchemyasp.com/permalink/v1/376_40150020909_1474_njpg/1/995/wnGIkIIu/document/",
"https://beta.alchemyasp.com/permalink/v1/Dockjpg/1/994/ajC6YQZ1/document/",
"https://beta.alchemyasp.com/permalink/v1/Oryx-Antelopejpg/1/993/wg7RpBNW/document/",
"https://beta.alchemyasp.com/permalink/v1/Gardenjpg/1/992/7bF0fCzR/document/",
"https://beta.alchemyasp.com/permalink/v1/Forestjpg/1/991/hnJTlVKm/document/",
"https://beta.alchemyasp.com/permalink/v1/Forest-Flowersjpg/1/990/j9pMOxJ7/document/",
"https://beta.alchemyasp.com/permalink/v1/Frangipani-Flowersjpg/1/989/ocD1lO6T/document/",
"https://beta.alchemyasp.com/permalink/v1/Winter-Leavesjpg/1/988/FGELggN6/document/",
"https://beta.alchemyasp.com/permalink/v1/Autumn-Leavesjpg/1/987/hRfJ24aW/document/",
"https://beta.alchemyasp.com/permalink/v1/Desert-Landscapejpg/1/986/UUfuu2wi/document/",
"https://beta.alchemyasp.com/permalink/v1/Waterfalljpg/1/985/p9ycenzE/document/",
"https://beta.alchemyasp.com/permalink/v1/Humpback-Whalejpg/1/984/cuBnVd1G/document/",
"https://beta.alchemyasp.com/permalink/v1/Treejpg/1/983/E8kTLDOE/document/",
"https://beta.alchemyasp.com/permalink/v1/523786_10150594592715910_800785131_njpg/1/980/MUg2dqd6/document/",
"https://beta.alchemyasp.com/permalink/v1/305967_10150594592895910_2061486501_njpg/1/979/kRYqT7pw/document/",
"https://beta.alchemyasp.com/permalink/v1/number8jpg/1/977/7paBfmLP/document/",
"https://beta.alchemyasp.com/permalink/v1/number1jpg/1/976/xU0FLuRH/document/",
"https://beta.alchemyasp.com/permalink/v1/ford-mustang-gt-premium-2013jpg/1/975/LaV5b7YD/document/",
"https://beta.alchemyasp.com/permalink/v1/1967_ford_mustang_shelby_gt500-pic-46154jpeg/1/974/JUFBwD3b/document/",
"https://beta.alchemyasp.com/permalink/v1/chevrolet_camaro_ss_04jpg/1/973/MD1DQTan/document/",
"https://beta.alchemyasp.com/permalink/v1/ford_mustang_gt_04jpg/1/972/PzXJqtab/document/",
"https://beta.alchemyasp.com/permalink/v1/4209133580_2240075ba9jpg/1/971/XHoUyxkZ/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7707jpg/1/970/pzdRQUwn/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7704jpg/1/969/pbGBCat7/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7708jpg/1/968/kxro1M9z/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7703jpg/1/967/6WBjxMqF/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7706jpg/1/966/8Hn0ISEY/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7708JPG/1/965/LgG8f0G5/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7703JPG/1/964/FvnYhe2e/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7707JPG/1/962/E8FkRhFY/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7706JPG/1/961/k7HHRaM3/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7704JPG/1/960/RLeKNvhX/document/",
"https://beta.alchemyasp.com/permalink/v1/523780_10151140470540910_1876284111_njpg/1/946/2Z6z4Irt/document/",
"https://beta.alchemyasp.com/permalink/v1/Jellyfishjpg/1/941/zL09AyIS/document/",
"https://beta.alchemyasp.com/permalink/v1/20jpg/1/940/iQYUrl6p/document/",
"https://beta.alchemyasp.com/permalink/v1/19jpg/1/939/kZVEqUGs/document/",
"https://beta.alchemyasp.com/permalink/v1/18jpg/1/938/M72oqA9N/document/",
"https://beta.alchemyasp.com/permalink/v1/17jpg/1/937/vpVKc3wV/document/",
"https://beta.alchemyasp.com/permalink/v1/16jpg/1/936/74RnNpFY/document/",
"https://beta.alchemyasp.com/permalink/v1/15jpg/1/935/4TM27wzQ/document/",
"https://beta.alchemyasp.com/permalink/v1/14jpg/1/934/1JfCpfEW/document/",
"https://beta.alchemyasp.com/permalink/v1/13jpg/1/933/mJzsSpAe/document/",
"https://beta.alchemyasp.com/permalink/v1/12jpg/1/932/BzlYzivZ/document/",
"https://beta.alchemyasp.com/permalink/v1/11jpg/1/931/1hg7zVGo/document/",
"https://beta.alchemyasp.com/permalink/v1/10jpg/1/930/1DyStCvE/document/",
"https://beta.alchemyasp.com/permalink/v1/9jpg/1/929/AgCgnvaQ/document/",
"https://beta.alchemyasp.com/permalink/v1/8jpg/1/928/LcuWUT1c/document/",
"https://beta.alchemyasp.com/permalink/v1/7jpg/1/927/VUcsXz6U/document/",
"https://beta.alchemyasp.com/permalink/v1/6jpg/1/926/Puca2yGz/document/",
"https://beta.alchemyasp.com/permalink/v1/5jpg/1/925/mkHnEgon/document/",
"https://beta.alchemyasp.com/permalink/v1/4jpg/1/924/E0vcUjAX/document/",
"https://beta.alchemyasp.com/permalink/v1/2jpg/1/923/pdoIYl8L/document/",
"https://beta.alchemyasp.com/permalink/v1/site-pro-page2jpg/1/922/DPJRmuZb/document/",
"https://beta.alchemyasp.com/permalink/v1/site-pro-page1jpg/1/921/I6PpKSs2/document/",
"https://beta.alchemyasp.com/permalink/v1/site-pro-page0jpg/1/920/y4J5xDGl/document/",
"https://beta.alchemyasp.com/permalink/v1/site-pro-2-intro2jpg/1/919/kNOSmg6T/document/",
"https://beta.alchemyasp.com/permalink/v1/site-pro-2-axe1-INTROjpg/1/918/1cC3uUoy/document/",
"https://beta.alchemyasp.com/permalink/v1/Penguinsjpg/1/917/QHWiHxWN/document/",
"https://beta.alchemyasp.com/permalink/v1/Lighthousejpg/1/916/S0lFzJS6/document/",
"https://beta.alchemyasp.com/permalink/v1/Koalajpg/1/915/CijzKTpU/document/",
"https://beta.alchemyasp.com/permalink/v1/Hydrangeasjpg/1/914/hTsrCIfq/document/",
"https://beta.alchemyasp.com/permalink/v1/Desertjpg/1/913/GxG9xJS2/document/",
"https://beta.alchemyasp.com/permalink/v1/Chrysanthemumjpg/1/912/82ZbtOCR/document/",
"https://beta.alchemyasp.com/permalink/v1/Tulipsjpg/1/911/Jq4VHTyC/document/",
"https://beta.alchemyasp.com/permalink/v1/Jellyfishjpg/1/910/qId9eHnZ/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-9jpg/1/909/q4adT2Ng/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-50jpg/1/907/ebAQOB9o/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-47jpg/1/906/awMKbtGa/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-45jpg/1/905/vp4Qlcav/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-42jpg/1/904/qJNdyhfU/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-32jpg/1/903/7Pceling/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-18jpg/1/902/jITdqAAx/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-17jpg/1/901/5SVbp3nA/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-6jpg/1/900/7GrarmsD/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-3jpg/1/899/Arjqjkqa/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-2jpg/1/898/SlJ4Fgdj/document/",
"https://beta.alchemyasp.com/permalink/v1/Smoke_V1_1000jpg/1/897/0eZ6HGK1/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7433JPG/1/859/0bBa21sO/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7432JPG/1/858/nuBp0HW4/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7431JPG/1/857/HH4uw7Jz/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7430JPG/1/856/NK9ZZcZx/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7428JPG/1/855/EKzatxE6/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7427JPG/1/854/zROdlw7U/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7426JPG/1/853/aIRL5n7r/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7425JPG/1/852/g0NucHEV/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7424JPG/1/851/ELXQehor/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7423JPG/1/850/4rhi8vNy/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7422JPG/1/849/UEop6fsC/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7421JPG/1/848/0pdiKNuB/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7419JPG/1/847/nLVZRiQp/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7417JPG/1/846/T2qGLvx1/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7416jpg/1/845/NScx3mHN/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7415JPG/1/844/KwamLDmB/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_7412JPG/1/843/UxRWU0Jw/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_2830jpg/1/842/4uDagjNL/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_2829jpg/1/841/aUxdARP7/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_2839jpg/1/840/60KtJeD3/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_2861jpg/1/839/IoRMQR9d/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_2840jpg/1/838/dXnaheF6/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_2864jpg/1/837/b9QYzfLu/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_2862jpg/1/836/F4hjMFg3/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_2869jpg/1/835/YVbfzlBj/document/",
"https://beta.alchemyasp.com/permalink/v1/Capture-decran-2012-05-22-a-154304png/1/831/vfzIxC8d/document/",
"https://beta.alchemyasp.com/permalink/v1/20070705_10003_off_2ORFjpg/1/830/OnggCbF8/document/",
"https://beta.alchemyasp.com/permalink/v1/20070705_10002_off_2ORFjpg/1/828/DcJC7rhw/document/",
"https://beta.alchemyasp.com/permalink/v1/20070705_10001_off_2ORFjpg/1/826/sPEeftBB/document/",
"https://beta.alchemyasp.com/permalink/v1/1D_RAWtif/1/824/i7oZQQ1E/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-50jpg/1/806/eUusotv8/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-35jpg/1/805/ZyuX6xFr/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-33jpg/1/804/x31HUmvd/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-32jpg/1/803/6nBCi0bp/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-30jpg/1/802/dpDVA2xw/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-11jpg/1/801/sL04bMqG/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-3jpg/1/800/K4PULhSW/document/",
"https://beta.alchemyasp.com/permalink/v1/amazing-animal-pictures-2jpg/1/799/nfNnFMyY/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_9995JPG/1/795/LBwcmrBq/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_0297JPG/1/794/3nHKMFWN/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_0008JPG/1/793/dIK3ZZfc/document/",
"https://beta.alchemyasp.com/permalink/v1/137696_thumbnailGIFgif/1/792/ieOOMFZo/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_5639JPG/1/790/UHUulae9/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_5685JPG/1/789/Wlai2sQF/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_5643JPG/1/788/9KgdIxvI/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_5634JPG/1/787/3pgPSrP3/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_5655JPG/1/786/eBnDUh6D/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_5645JPG/1/785/WRmn0eAd/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_5649JPG/1/784/netayZei/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_5632JPG/1/783/JukMvqi6/document/",
"https://beta.alchemyasp.com/permalink/v1/Abbaye-des-Chateliers/1/782/Woka8WEQ/document/",
"https://beta.alchemyasp.com/permalink/v1/Abbaye-des-Chateliers/1/781/U4h7lKiq/document/",
"https://beta.alchemyasp.com/permalink/v1/Abbaye-des-Chateliers/1/780/TtivCz3l/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_5653JPG/1/779/mmkOInuV/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_5654JPG/1/778/I7XtLjcx/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_5480jpg/1/777/Dc7CB4J1/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_5473jpg/1/776/0QvIAjCv/document/",
"https://beta.alchemyasp.com/permalink/v1/IMG_5485JPG/1/775/X2k3V6OG/document/",
"https://beta.alchemyasp.com/permalink/v1/iconesai/1/772/YsO99J9R/document/",
"https://beta.alchemyasp.com/permalink/v1/New-york/1/752/w0ix2YQ4/document/",
"https://beta.alchemyasp.com/permalink/v1/New-york-new-york/1/751/yWeUZ2Bg/document/",
"https://beta.alchemyasp.com/permalink/v1/new-york/1/750/MbUbrpL1/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_036jpg/1/749/7ik18Tjz/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_035jpg/1/748/uNNytMEy/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_034jpg/1/747/MYCD76kV/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_033jpg/1/746/L9qvgGLL/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_032jpg/1/745/LZekypsa/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_031jpg/1/744/0tUojThO/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_030jpg/1/743/uAWDt5S7/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_029jpg/1/742/GugRRwr7/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_028jpg/1/741/3GO92cip/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_027jpg/1/740/4VctTfl4/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_026jpg/1/739/v5a5DTln/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_025jpg/1/738/77d18spg/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_024jpg/1/737/aVx5Pfvv/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_023jpg/1/736/pNgQrfXl/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_022jpg/1/735/N6F4nemw/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_021jpg/1/734/npgpCqzZ/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_020jpg/1/733/uFZlB0Qi/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_019jpg/1/732/ELuu78NZ/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_018jpg/1/731/VekLEJcw/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_017jpg/1/730/jik6cgTX/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_016jpg/1/729/aOLR2bav/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_015jpg/1/728/Ib0kQV2B/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_014jpg/1/727/miOXBd7b/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_013jpg/1/726/cGZ6HqDY/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_012jpg/1/725/4wdIMkfA/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_011jpg/1/724/lcYWkezS/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_010jpg/1/723/8SICNC9o/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_009jpg/1/722/eVY0Wowr/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_008jpg/1/721/HDvdhyIJ/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_007jpg/1/720/Lsp3kgiW/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_006jpg/1/719/EKAP8HnO/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_005jpg/1/718/DBtFJsb3/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_004jpg/1/717/u75hC17T/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_003jpg/1/716/puCJAoO7/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_002jpg/1/715/I5QooEop/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_001jpg/1/714/iwRBppjv/document/",
"https://beta.alchemyasp.com/permalink/v1/New_York_000jpg/1/713/c9H6r77Y/document/",
"https://beta.alchemyasp.com/permalink/v1/madereJPG/1/709/cdfmhGwC/document/",
"https://beta.alchemyasp.com/permalink/v1/lofotenJPG/1/708/FyhLlaSw/document/",
"https://beta.alchemyasp.com/permalink/v1/marseillejpg/1/707/uWV43BKe/document/",
"https://beta.alchemyasp.com/permalink/v1/madere-2jpg/1/706/TBd7Y2XI/document/",
"https://beta.alchemyasp.com/permalink/v1/palette_action_ENjpg/1/705/XXxchABd/document/",
"https://beta.alchemyasp.com/permalink/v1/palette_action_DEjpg/1/704/zF96DgYH/document/",
"https://beta.alchemyasp.com/permalink/v1/minilogop4jpg/1/703/BRGYMCuf/document/",
"https://beta.alchemyasp.com/permalink/v1/ZUIL18653-2012CL01jpg/1/701/wsygvktv/document/",
"https://beta.alchemyasp.com/permalink/v1/YANG14413-2007NB84jpg/1/700/gj3rsdN2/document/",
"https://beta.alchemyasp.com/permalink/v1/DUMA18558-2011NB02jpg/1/699/lKadEq2K/document/",
"https://beta.alchemyasp.com/permalink/v1/ZUIL18653-2012CL01jpg/1/698/PgcS4T0L/document/",
"https://beta.alchemyasp.com/permalink/v1/ML_MG_7771jpg/1/697/Wccgegmh/document/",
"https://beta.alchemyasp.com/permalink/v1/Chai-centaure-3JPG/1/696/4XW0Fqbw/document/",
"https://beta.alchemyasp.com/permalink/v1/push-layergif/1/692/9DhAjyWT/document/",
"https://beta.alchemyasp.com/permalink/v1/capturegif/1/691/pyup249E/document/",
"https://beta.alchemyasp.com/permalink/v1/TRAFIC-MARITIME-SUR-L-ESTUAIRE-DE-LA-GIRONDE-MEDOC-COTE-ATLANTIQ/1/687/8FpZx3N7/document/",
"https://beta.alchemyasp.com/permalink/v1/PICTOGRAMME-D-INTERDICTION-D-URINER/1/686/CA04HFXB/document/",
"https://beta.alchemyasp.com/permalink/v1/PICTOGRAMME-D-INTERDICTION-D-URINER/1/685/wqJXhEjE/document/",
"https://beta.alchemyasp.com/permalink/v1/00A05036jpg/1/684/NMFylv0S/document/",
"https://beta.alchemyasp.com/permalink/v1/00A05034jpg/1/683/ho37fAry/document/",
"https://beta.alchemyasp.com/permalink/v1/00A05102jpg/1/682/XQzljMdW/document/",
"https://beta.alchemyasp.com/permalink/v1/00A05033jpg/1/681/54js09QL/document/",
"https://beta.alchemyasp.com/permalink/v1/00A05030jpg/1/680/Iss5mv6M/document/",
        );



        foreach ($images as $image) {

//        $ids = array();
//        $n = 235;
//        while($n > 0) {
//            $n--;
            $jobset = new \Gloubster\Documents\JobSet();
//            $jobset->setFile('http://192.168.1.196/photo03.JPG');
//            $jobset->setFile('https://beta.alchemyasp.com/photo03.JPG');
//            $jobset->setFile('https://beta.alchemyasp.com/gray.jpg');
            $jobset->setFile($image);

            $specification = new \Gloubster\Documents\Specification();
            $specification->setName('image');


//            $timer = new \Gloubster\Documents\Timer();
//            $timer->setName('hip');
//            $timer->setValue(12.45);
//            $dm->persist($timer);
//            $specification->addTimers($timer);

            $parameter = new \Gloubster\Documents\Parameter();
            $parameter->setName('width')->setValue(64);
            $parameter->setName('height')->setValue(64);
            $dm->persist($parameter);
            $specification->addParameters($parameter);

            $specification->setJobset($jobset);
            $jobset->addSpecifications($specification);
            $dm->persist($specification);

            $ids [] = $specification->getId();


            $specification = new \Gloubster\Documents\Specification();
            $specification->setName('image');


//            $timer = new \Gloubster\Documents\Timer();
//            $timer->setName('hip');
//            $timer->setValue(12.45);
//            $dm->persist($timer);
//            $specification->addTimers($timer);

            $parameter = new \Gloubster\Documents\Parameter();
            $parameter->setName('width')->setValue(32);
            $parameter->setName('height')->setValue(32);
            $dm->persist($parameter);
            $specification->addParameters($parameter);

            $specification->setJobset($jobset);
            $jobset->addSpecifications($specification);
            $dm->persist($specification);

            $ids [] = $specification->getId();


            $specification = new \Gloubster\Documents\Specification();
            $specification->setName('image');


//            $timer = new \Gloubster\Documents\Timer();
//            $timer->setName('hip');
//            $timer->setValue(12.45);
//            $dm->persist($timer);
//            $specification->addTimers($timer);


            $parameter = new \Gloubster\Documents\Parameter();
            $parameter->setName('width')->setValue(16);
            $parameter->setName('height')->setValue(16);
            $dm->persist($parameter);
            $specification->addParameters($parameter);

            $specification->setJobset($jobset);
            $jobset->addSpecifications($specification);
            $dm->persist($specification);


            $ids [] = $specification->getId();

            $dm->persist($jobset);
        }

        $dm->flush();

//        echo "next\n";
//
//        foreach($dm->getRepository('Gloubster\Documents\Specification')->findAll() as $spec) {
//            $timer = new \Gloubster\Documents\Timer();
//            $timer->setName('hop');
//            $timer->setValue(12.45);
//            $dm->persist($timer);
//            $spec->addTimers($timer);
//
//            $dm->persist($spec);
//            $dm->flush();
//        }

    }
}
