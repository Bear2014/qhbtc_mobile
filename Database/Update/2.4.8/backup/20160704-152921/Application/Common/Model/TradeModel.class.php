<?php
    namespace Common\Model;
    use Think\Model;
    class TradeModel extends Model
    {
        protected $keyS = 'Trade';

        //模拟交易
        public function moni($market = null)
        {
            if (empty($market)) {
                return null;
            }
            $userid = rand(1, 10);
            $type = rand(1, 2);
            $min_price = round((C('market')[$market]['buy_min']) * 100000);
            $max_price = round((C('market')[$market]['buy_max']) * 100000);
            $price = round(rand($min_price, $max_price) / 100000, C('market')[$market]['round']);
            $max_num = round(C('market')[$market]['trade_max'] / C('market')[$market]['buy_max'] * 10000, (8 - C('market')[$market]['round']));
            $min_num = round(1 / C('market')[$market]['buy_max'] * 10000, (8 - C('market')[$market]['round']));
            $num = round(abs(rand($min_num, $max_num)) / 10000, (8 - C('market')[$market]['round']));
            /**
             * ************检查数据格式************
             */
            // 检查交易价格
            if (!$price) {
                return ('交易价格格式错误');
            }
            // 检查交易数量
            if (!check($num, 'double')) {
                return ('交易数量格式错误' . $num);
            }
            // 检查交易类型
            if ($type != 1 && $type != 2) {
                return ('交易类型格式错误');
            }
            // 得到交易市场
            if (!C('market')[$market]) {
                return ('交易市场错误');
            } else {
                $xnb = explode("_", $market)[0];
                $rmb = explode("_", $market)[1];
            }
            // 检查是否可以交易
            if (!C('market')[$market]['trade']) {
                return ('当前市场禁止交易');
            }
            /**
             * ************数据格式化************
             */
            $price = round(floatval($price), C('market')[$market]['round']);
            // 再检查一次价格
            if (!$price) {
                return ('交易价格错误');
            }
            $num = round(trim($num), 8 - C('market')[$market]['round']);
            // 再检查一次价格
            if (!check($num, 'double')) {
                return ('交易数量错误');
            }
            /**
             * ************检查价格范围************
             */
            if ($type == 1) {
                $min_price = C('market')[$market]['buy_min'] ? C('market')[$market]['buy_min'] : 0.00000001;
                $max_price = C('market')[$market]['buy_max'] ? C('market')[$market]['buy_max'] : 10000000;
            } elseif ($type == 2) {
                $min_price = C('market')[$market]['sell_min'] ? C('market')[$market]['sell_min'] : 0.00000001;
                $max_price = C('market')[$market]['sell_max'] ? C('market')[$market]['sell_max'] : 10000000;
            } else {
                return ('交易类型错误');
            }
            if ($price > $max_price) {
                return ('交易价格超过最大限制！');
            }
            if ($price < $min_price) {
                return ('交易价格超过最小限制！' . $price . '-' . $min_price);
            }
            /**
             * ************检查涨跌范围************
             */
            // 最后价
            $hou_price = C('market')[$market]['hou_price'];
            if ($hou_price) {
                /*
                // 涨检查
                if(C('market')[$market]['zhang']){
                    $zhang_price=round($hou_price/100*(100+C('market')[$market]['zhang']));
                    if($price>$zhang_price){
                        return ('交易价格超过今日涨幅限制！');
                    }
                }
                // 跌检查
                if(C('market')[$market]['die']){
                    $die_price=round($hou_price/100*(100-C('market')[$market]['die']));
                    if($price<$die_price){
                        return ('交易价格超过今日跌幅限制！');
                    }
                }*/
            }
            /**
             * ************计算手续费************
             */
            $user_coin = M('UserCoin')->where(array('userid' => $userid))->find();
            // 得到币种列表
            if ($type == 1) {
                $trade_fee = C('market')[$market]['fee_buy'];
                if ($trade_fee) {
                    $fee = round($num * $price / 100 * $trade_fee, 8);
                    $mum = round($num * $price / 100 * (100 + $trade_fee), 8);
                } else {
                    $fee = 0;
                    $mum = round($num * $price, 8);
                }
                if ($user_coin[$rmb] < $mum) {
                    return (C('coin')[$rmb]['title'] . '余额不足！');
                }
            } elseif ($type == 2) {
                $trade_fee = C('market')[$market]['fee_sell'];
                if ($trade_fee) {
                    $fee = round($num * $price / 100 * $trade_fee, 8);
                    $mum = round($num * $price / 100 * (100 - $trade_fee), 8);
                } else {
                    $fee = 0;
                    $mum = round($num * $price, 8);
                }
                if ($user_coin[$xnb] < $num) {
                    return (C('coin')[$xnb]['title'] . '余额不足2！');
                }
            } else {
                return ('交易类型错误');
            }
            /**
             * ***********挂单比例计算**********单独功能************代码开始*
             */
            if (C('coin')[$xnb]['fee_bili']) {
                if ($type == 2) {
                    // 用户当前币种持币总量
                    $bili_user = round(($user_coin[$xnb] + $user_coin[$xnb . 'd']), 8);
                    if ($bili_user) {
                        // 用户可以交易的数量
                        $bili_keyi = round(($bili_user / 100 * C('coin')[$xnb]['fee_bili']), 8);
                        if ($bili_keyi) {
                            // 用户目前正在挂单的数量
                            $bili_zheng = M()->query('select id,price,sum(num-deal)as nums from movesay_trade where userid=' . userid() . ' and status=0 and type=2 and market like \'%' . $xnb . '%\' ;');
                            // 转0计算
                            if (!$bili_zheng[0]['nums']) {
                                $bili_zheng[0]['nums'] = 0;
                            }
                            // 用户可以挂单的数量
                            $bili_kegua = $bili_keyi - $bili_zheng[0]['nums'];
                            // 转0计算
                            if ($bili_kegua < 0) {
                                $bili_kegua = 0;
                            }
                            // 检查当前数量超过限制
                            if ($num > $bili_kegua) {
                                return ('您的挂单总数量超过系统限制，您当前持有' . C('coin')[$xnb]['title'] . $bili_user . '个，已经挂单' . $bili_zheng[0]['nums'] . '个，还可以挂单' . $bili_kegua . '个');
                            }
                        } else {
                            return ('可交易量错误');
                        }
                    }
                }
            }
            /**
             * ************检查单笔挂单额度************
             */
            if (C('market')[$market]['trade_min']) {
                if ($mum < C('market')[$market]['trade_min']) {
                    return ('交易总额不能小于' . C('market')[$market]['trade_min']);
                }
            }
            if (C('market')[$market]['trade_max']) {
                if ($mum > C('market')[$market]['trade_max']) {
                    return ('交易总额不能大于' . C('market')[$market]['trade_max']);
                }
            }
            /**
             * ************最后检查一次数据************
             */
            if (!$rmb) {
                return ('数据错误1');
            }
            if (!$xnb) {
                return ('数据错误2');
            }
            if (!$market) {
                return ('数据错误3');
            }
            if (!$price) {
                return ('数据错误4');
            }
            if (!$num) {
                return ('数据错误5');
            }
            if (!$mum) {
                return ('数据错误6');
            }
            if (!$type) {
                return ('数据错误7');
            }
            /**
             * ************开始挂单处理************
             */
            $mo = M();
            $mo->execute('set autocommit=0');
            $mo->execute('lock tables movesay_trade write ,movesay_user_coin write,movesay_finance write');
            $rs = array();
            if ($type == 1) {
                //先查询一次账号余额
                $finance = $mo->table('movesay_finance')->where(array('userid' => $userid))->order('id desc')->find();
                $finance_num_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $userid))->find();
                // 减少正常人民币
                $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $userid))->setDec($rmb, $mum);
                // 增加冻结人民币
                $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $userid))->setInc($rmb . 'd', $mum);
                // 添加订单记录
                $rs[] = $finance_nameid = $mo->table('movesay_trade')->add(array(
                    'userid'  => $userid,
                    'market'  => $market,
                    'price'   => $price,
                    'num'     => $num,
                    'mum'     => $mum,
                    'fee'     => $fee,
                    'type'    => 1,
                    'addtime' => time(),
                    'status'  => 0,
                ));
                //添加财务明细
                $finance_mum_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $userid))->find();
                $finance_hash = md5($userid . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.movesay.com');
                $finance_num = ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']);
                if ($finance_num > $finance['mum']) {
                    $finance_status = ($finance_num - $finance['mum']) > 1 ? 0 : 1;
                } else {
                    $finance_status = ($finance['mum'] - $finance_num) > 1 ? 0 : 1;
                }
                $rs[] = $mo->table('movesay_finance')->add(array(
                    'userid'   => $userid,
                    'coinname' => 'cny',
                    'num_a'    => $finance_num_user_coin['cny'],
                    'num_b'    => $finance_num_user_coin['cnyd'],
                    'num'      => ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']),
                    'fee'      => $mum,
                    'type'     => 2,
                    'name'     => 'trade',
                    'nameid'   => $finance_nameid,
                    'remark'   => '交易中心-委托买入-市场' . $market,
                    'mum_a'    => $finance_mum_user_coin['cny'],
                    'mum_b'    => $finance_mum_user_coin['cnyd'],
                    'mum'      => ($finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd']),
                    'move'     => $finance_hash,
                    'addtime'  => time(),
                    'status'   => $finance_status,
                ));
            } elseif ($type == 2) {
                // 减少正常虚拟币
                $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $userid))->setDec($xnb, $num);
                // 增加冻结虚拟币
                $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $userid))->setInc($xnb . 'd', $num);
                // 添加订单记录
                $rs[] = $mo->table('movesay_trade')->add(array(
                    'userid'  => $userid,
                    'market'  => $market,
                    'price'   => $price,
                    'num'     => $num,
                    'mum'     => $mum,
                    'fee'     => $fee,
                    'type'    => 2,
                    'addtime' => time(),
                    'status'  => 0,
                ));
            } else {
                $mo->execute('rollback');
                $mo->execute('unlock tables');
                return ('交易类型错误');
            }
            if (check_arr($rs)) {
                $mo->execute('commit');
                $mo->execute('unlock tables');
                $this->dapan($market); // 大盘处理
                return ('交易成功！');
            } else {
                $mo->execute('rollback');
                $mo->execute('unlock tables');
                mlog('bb ' . implode("|", $rs));
                return null;
            }
        }

        //大盘
        public function dapan($market = null)
        {
            if (!$market) {
                return false;
            } else {
                $xnb = explode("_", $market)[0];
                $rmb = explode("_", $market)[1];
            }
            $fee_buy = C('market')[$market]['fee_buy'];
            $fee_sell = C('market')[$market]['fee_sell'];
            $invit_buy = C('market')[$market]['invit_buy'];
            $invit_sell = C('market')[$market]['invit_sell'];
            $invit_1 = C('market')[$market]['invit_1'];
            $invit_2 = C('market')[$market]['invit_2'];
            $invit_3 = C('market')[$market]['invit_3'];
            // 实例化模型
            $mo = M();
            $mo->table('movesay_trade')->where('num<deal')->setField('status', 1);
            $new_trade_movesay = 0;
            // 开始循环计算
            for ($i = 1; $i < 10; $i++) {
                // 当前最大买入数据
                $buy = $mo->table('movesay_trade')->where(array(
                    'market' => $market,
                    'type'   => 1,
                    'status' => 0,
                ))->order('price desc,id asc')->find();
                // 当前最小卖出数据
                $sell = $mo->table('movesay_trade')->where(array(
                    'market' => $market,
                    'type'   => 2,
                    'status' => 0,
                ))->order('price asc,id asc')->find();
                if ($buy['id'] > $sell['id']) {
                    $type = 1;
                } else {
                    $type = 2;
                }
                // 比较是否可以成交
                if ($buy && $sell && (floatval($buy['price']) - floatval($sell['price'])) >= 0) {
                    // 初始化设置
                    $rs = array();
                    if ($buy['num'] <= $buy['deal']) {
                        //M('Trade')->where(array ('id'=>$buy['id'] ))->setField('status',1);
                        //break;
                    }
                    if ($sell['num'] <= $sell['deal']) {
                        //M('Trade')->where(array ('id'=>$sell['id'] ))->setField('status',1);
                        //break;
                    }
                    // 得到成交数量
                    $amount = min(round(($buy['num'] - $buy['deal']), (8 - C('market')[$market]['round'])), round(($sell['num'] - $sell['deal']), (8 - C('market')[$market]['round'])));
                    $amount = round($amount, (8 - C('market')[$market]['round']));
                    if ($amount <= 0) {
                        $log = '错误1交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . "\n";
                        $log .= 'ERR: 成交数量出错，数量是' . $amount;
                        mlog($log);
                        M('Trade')->where(array('id' => $buy['id']))->setField('status', 1);
                        M('Trade')->where(array('id' => $sell['id']))->setField('status', 1);
                        break;
                    }
                    // 得到成交价格
                    if ($type == 1) {
                        $price = $sell['price']; // 成交价格为卖单的价格
                    } elseif ($type == 2) {
                        $price = $buy['price']; // 成交价格为买单的价格
                    } else {
                        break;
                    }
                    if (!$price) {
                        $log = '错误2交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . "\n";
                        $log .= 'ERR: 成交价格出错，价格是' . $price;
                        mlog($log);
                        break;
                    } else {
                        $price = round($price, C('market')[$market]['round']);
                    }
                    // 得到成交额
                    $mum = round(($price * $amount), 8);
                    if (!$mum) {
                        $log = '错误3交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . "\n";
                        $log .= 'ERR: 成交总额出错，总额是' . $mum;
                        mlog($log);
                        break;
                    } else {
                        $mum = round($mum, 8);
                    }
                    // 买家更新计算
                    if ($fee_buy) {
                        $buy_fee = round(($mum / 100 * $fee_buy), 8);
                        $buy_save = round(($mum / 100 * (100 + $fee_buy)), 8);
                    } else {
                        $buy_fee = 0;
                        $buy_save = $mum;
                    }
                    if (!$buy_save) {
                        $log = '错误4交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                        $log .= 'ERR: 买家更新数量出错，更新数量是' . $buy_save;
                        mlog($log);
                        break;
                    }
                    // 卖家更新计算
                    if ($fee_sell) {
                        $sell_fee = round(($mum / 100 * $fee_sell), 8);
                        $sell_save = round(($mum / 100 * (100 - $fee_sell)), 8);
                    } else {
                        $sell_fee = 0;
                        $sell_save = $mum;
                    }
                    if (!$sell_save) {
                        $log = '错误5交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                        $log .= 'ERR: 卖家更新数量出错，更新数量是' . $sell_save;
                        mlog($log);
                        break;
                    }
                    // 得到买家财产
                    $user_buy = M('UserCoin')->where(array('userid' => $buy['userid']))->find();
                    if (!$user_buy[$rmb . 'd']) {
                        $log = '错误6交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                        $log .= 'ERR: 买家财产错误，冻结财产是' . $user_buy[$rmb . 'd'];
                        mlog($log);
                        break;
                    }
                    // 得到卖家财产
                    $user_sell = M('UserCoin')->where(array('userid' => $sell['userid']))->find();
                    if (!$user_sell[$xnb . 'd']) {
                        $log = '错误7交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                        $log .= 'ERR: 卖家财产错误，冻结财产是' . $user_sell[$xnb . 'd'];
                        mlog($log);
                        break;
                    }
                    if ($user_buy[$rmb . 'd'] < 0.00000001) {
                        $log = '错误88交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                        $log .= 'ERR: 买家更新冻结人民币出现错误,应该更新' . $buy_save . '账号余额' . $user_buy[$rmb . 'd'] . '进行错误处理';
                        mlog($log);
                        M('Trade')->where(array('id' => $buy['id']))->setField('status', 1);
                        break;
                    }
                    // 买家更新比较
                    if (round($user_buy[$rmb . 'd'], 8) >= $buy_save) {
                        $save_buy_rmb = $buy_save;
                    } else {
                        if ((round($user_buy[$rmb . 'd'], 8) + 1) >= $buy_save) {
                            $save_buy_rmb = $user_buy[$rmb . 'd'];
                            $log = '错误8交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                            $log .= 'ERR: 买家更新冻结人民币出现误差,应该更新' . $buy_save . '账号余额' . $user_buy[$rmb . 'd'] . '实际更新' . $save_buy_rmb;
                            mlog($log);
                        } else {
                            $log = '错误9交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                            $log .= 'ERR: 买家更新冻结人民币出现错误,应该更新' . $buy_save . '账号余额' . $user_buy[$rmb . 'd'] . '进行错误处理';
                            mlog($log);
                            M('Trade')->where(array('id' => $buy['id']))->setField('status', 1);
                            break;
                        }
                    }
                    // 卖家更新比较
                    if (round($user_sell[$xnb . 'd'], C('market')[$market]['round']) >= $amount) {
                        $save_sell_xnb = $amount;
                    } else {
                        if ((round($user_sell[$xnb . 'd'], C('market')[$market]['round']) + 1) >= $amount) {
                            $save_sell_xnb = $user_sell[$xnb . 'd'];
                            $log = '错误10交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                            $log .= 'ERR: 卖家更新冻结虚拟币出现误差,应该更新' . $amount . '账号余额' . $user_sell[$xnb . 'd'] . '实际更新' . $save_sell_xnb;
                            mlog($log);
                        } else {
                            $log = '错误11交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                            $log .= 'ERR: 卖家更新冻结虚拟币出现错误,应该更新' . $amount . '账号余额' . $user_sell[$xnb . 'd'] . '进行错误处理';
                            mlog($log);
                            M('Trade')->where(array('id' => $sell['id']))->setField('status', 1);
                            break;
                        }
                    }
                    //买家更新数量出错
                    if (!$save_buy_rmb) {
                        $log = '错误12交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                        $log .= 'ERR: 买家更新数量出错错误,更新数量是' . $save_buy_rmb;
                        mlog($log);
                        M('Trade')->where(array('id' => $buy['id']))->setField('status', 1);
                        break;
                    }
                    //卖家更新数量出错
                    if (!$save_sell_xnb) {
                        $log = '错误13交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                        $log .= 'ERR: 卖家更新数量出错错误,更新数量是' . $save_sell_xnb;
                        mlog($log);
                        M('Trade')->where(array('id' => $sell['id']))->setField('status', 1);
                        break;
                    }
                    // 禁止自动提交
                    $mo->execute('set autocommit=0');
                    // 执行锁表操作
                    $mo->execute('lock tables movesay_trade write ,movesay_trade_log write ,movesay_user write, movesay_user_coin write,movesay_invit write ,movesay_finance write');
                    // 更新买家订单数据
                    $rs[] = $mo->table('movesay_trade')->where(array('id' => $buy['id']))->setInc('deal', $amount);
                    // 更新卖家订单数据
                    $rs[] = $mo->table('movesay_trade')->where(array('id' => $sell['id']))->setInc('deal', $amount);
                    // 添加交易记录
                    $rs[] = $finance_nameid = $mo->table('movesay_trade_log')->add(array(
                        'userid'   => $buy['userid'],
                        'peerid'   => $sell['userid'],
                        'market'   => $market,
                        'price'    => $price,
                        'num'      => $amount,
                        'mum'      => $mum,
                        'type'     => $type,
                        'fee_buy'  => $buy_fee,
                        'fee_sell' => $sell_fee,
                        'addtime'  => time(),
                        'status'   => 1,
                    ));
                    // 更新买家人民币
                    $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $buy['userid']))->setInc($xnb, $amount);
                    //先查询一次账号余额
                    $finance = $mo->table('movesay_finance')->where(array('userid' => $buy['userid']))->order('id desc')->find();
                    $finance_num_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $buy['userid']))->find();
                    // 更新买家虚拟币
                    $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $buy['userid']))->setDec($rmb . 'd', $save_buy_rmb);
                    //添加财务明细
                    $finance_mum_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $buy['userid']))->find();
                    $finance_hash = md5($buy['userid'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.movesay.com');
                    $finance_num = ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']);
                    if ($finance_num > $finance['mum']) {
                        $finance_status = ($finance_num - $finance['mum']) > 1 ? 0 : 1;
                    } else {
                        $finance_status = ($finance['mum'] - $finance_num) > 1 ? 0 : 1;
                    }
                    $rs[] = $mo->table('movesay_finance')->add(array(
                        'userid'   => $buy['userid'],
                        'coinname' => 'cny',
                        'num_a'    => $finance_num_user_coin['cny'],
                        'num_b'    => $finance_num_user_coin['cnyd'],
                        'num'      => ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']),
                        'fee'      => $save_buy_rmb,
                        'type'     => 2,
                        'name'     => 'tradelog',
                        'nameid'   => $finance_nameid,
                        'remark'   => '交易中心-成功买入-市场' . $market,
                        'mum_a'    => $finance_mum_user_coin['cny'],
                        'mum_b'    => $finance_mum_user_coin['cnyd'],
                        'mum'      => ($finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd']),
                        'move'     => $finance_hash,
                        'addtime'  => time(),
                        'status'   => $finance_status,
                    ));
                    //先查询一次账号余额
                    $finance = $mo->table('movesay_finance')->where(array('userid' => $sell['userid']))->order('id desc')->find();
                    $finance_num_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $sell['userid']))->find();
                    // 更新卖家人民币
                    $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $sell['userid']))->setInc($rmb, $sell_save);
                    //添加财务明细
                    $save_buy_rmb = $sell_save;
                    $finance_mum_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $sell['userid']))->find();
                    $finance_hash = md5($sell['userid'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.movesay.com');
                    $finance_num = ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']);
                    if ($finance_num > $finance['mum']) {
                        $finance_status = ($finance_num - $finance['mum']) > 1 ? 0 : 1;
                    } else {
                        $finance_status = ($finance['mum'] - $finance_num) > 1 ? 0 : 1;
                    }
                    $rs[] = $mo->table('movesay_finance')->add(array(
                        'userid'   => $sell['userid'],
                        'coinname' => 'cny',
                        'num_a'    => $finance_num_user_coin['cny'],
                        'num_b'    => $finance_num_user_coin['cnyd'],
                        'num'      => ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']),
                        'fee'      => $save_buy_rmb,
                        'type'     => 1,
                        'name'     => 'tradelog',
                        'nameid'   => $finance_nameid,
                        'remark'   => '交易中心-成功卖出-市场' . $market,
                        'mum_a'    => $finance_mum_user_coin['cny'],
                        'mum_b'    => $finance_mum_user_coin['cnyd'],
                        'mum'      => ($finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd']),
                        'move'     => $finance_hash,
                        'addtime'  => time(),
                        'status'   => $finance_status,
                    ));
                    // 更新卖家虚拟币
                    $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $sell['userid']))->setDec($xnb . 'd', $save_sell_xnb);
                    // 计算交易完成
                    $buy_list = $mo->table('movesay_trade')->where(array(
                        'id'     => $buy['id'],
                        'status' => 0,
                    ))->find();
                    if ($buy_list) {
                        if ($buy_list['deal'] >= $buy_list['num']) {
                            $rs[] = $mo->table('movesay_trade')->where(array('id' => $buy['id']))->setField('status', 1);
                        }
                    }
                    $sell_list = $mo->table('movesay_trade')->where(array(
                        'id'     => $sell['id'],
                        'status' => 0,
                    ))->find();
                    if ($sell_list) {
                        if ($sell_list['deal'] >= $sell_list['num']) {
                            $rs[] = $mo->table('movesay_trade')->where(array('id' => $sell['id']))->setField('status', 1);
                        }
                    }
                    // 买价大于成交价买家退还冻结人民币差价
                    if ($buy['price'] > $price) {
                        // 按成交量计算总共冻结了多少钱
                        $chajia_dong = round(($amount * $buy['price'] / 100 * (100 + $fee_buy)), 8);
                        // 按成交量计算实际用了多少钱
                        $chajia_shiji = round(($amount * $price / 100 * (100 + $fee_buy)), 8);
                        // 得到差价进行解冻
                        $chajia = round(($chajia_dong - $chajia_shiji), 8);
                        // 确定有差价
                        if ($chajia) {
                            // 检查买家余额
                            $chajia_user_buy = $mo->table('movesay_user_coin')->where(array('userid' => $buy['userid']))->find();
                            // 检查买家冻结余额
                            if (round($chajia_user_buy[$rmb . 'd'], 8) >= $chajia) {
                                $chajia_save_buy_rmb = $chajia;
                            } else {
                                if ((round($chajia_user_buy[$rmb . 'd'], 8) + 1) >= $chajia) {
                                    // 误差1个币以内
                                    $chajia_save_buy_rmb = $chajia_user_buy[$rmb . 'd'];
                                    mlog('错误91交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount, '成交价格' . $price . '成交总额' . $mum . "\n");
                                    mlog('交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '成交数量' . $amount . '交易方式：' . $type . '卖家更新冻结虚拟币出现误差,应该更新' . $chajia . '账号余额' . $chajia_user_buy[$rmb . 'd'] . '实际更新' . $chajia_save_buy_rmb);
                                } else {
                                    mlog('错误92交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount, '成交价格' . $price . '成交总额' . $mum . "\n");
                                    // 误差数量太大////////////////////////////////////////////////////////////////////////需要处理
                                    mlog('交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '成交数量' . $amount . '交易方式：' . $type . '卖家更新冻结虚拟币出现错误,应该更新' . $chajia . '账号余额' . $chajia_user_buy[$rmb . 'd'] . '进行错误处理');
                                    $mo->execute('rollback');
                                    $mo->execute('unlock tables');
                                    M('Trade')->where(array('id' => $buy['id']))->setField('status', 1);
                                    M('Trade')->execute('commit');
                                    break;
                                }
                            }
                            if ($chajia_save_buy_rmb) {
                                //先查询一次账号余额
                                $finance = $mo->table('movesay_finance')->where(array('userid' => $buy['userid']))->order('id desc')->find();
                                $finance_num_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $buy['userid']))->find();
                                // 买家冻结人民币币更新
                                $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $buy['userid']))->setDec($rmb . 'd', $chajia_save_buy_rmb);
                                // 返给买家差价
                                $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $buy['userid']))->setInc($rmb, $chajia_save_buy_rmb);
                                //添加财务明细
                                $save_buy_rmb = $chajia_save_buy_rmb;
                                $finance_mum_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $buy['userid']))->find();
                                $finance_hash = md5($buy['userid'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.movesay.com');
                                $finance_num = ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']);
                                if ($finance_num > $finance['mum']) {
                                    $finance_status = ($finance_num - $finance['mum']) > 1 ? 0 : 1;
                                } else {
                                    $finance_status = ($finance['mum'] - $finance_num) > 1 ? 0 : 1;
                                }
                                $rs[] = $mo->table('movesay_finance')->add(array(
                                    'userid'   => $buy['userid'],
                                    'coinname' => 'cny',
                                    'num_a'    => $finance_num_user_coin['cny'],
                                    'num_b'    => $finance_num_user_coin['cnyd'],
                                    'num'      => ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']),
                                    'fee'      => $save_buy_rmb,
                                    'type'     => 1,
                                    'name'     => 'tradelog',
                                    'nameid'   => $finance_nameid,
                                    'remark'   => '交易中心-买家委托-退回' . $market,
                                    'mum_a'    => $finance_mum_user_coin['cny'],
                                    'mum_b'    => $finance_mum_user_coin['cnyd'],
                                    'mum'      => ($finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd']),
                                    'move'     => $finance_hash,
                                    'addtime'  => time(),
                                    'status'   => $finance_status,
                                ));
                            }
                        }
                    }
                    // 检查买家是否还有挂单
                    $you_buy = $mo->table('movesay_trade')->where(array(
                        'market' => array(
                            'like',
                            '%' . $rmb . '%',
                        ),
                        'status' => 0,
                        'userid' => $buy['userid'],
                    ))->find();
                    // 检查卖家是否还有挂单
                    $you_sell = $mo->table('movesay_trade')->where(array(
                        'market' => array(
                            'like',
                            '%' . $xnb . '%',
                        ),
                        'status' => 0,
                        'userid' => $sell['userid'],
                    ))->find();
                    // 买家没有挂单了
                    if (!$you_buy) {
                        // 检查买家余额
                        $you_user_buy = $mo->table('movesay_user_coin')->where(array('userid' => $buy['userid']))->find();
                        if ($you_user_buy[$rmb . 'd'] > 0) {
                            //先查询一次账号余额
                            $finance = $mo->table('movesay_finance')->where(array('userid' => $buy['userid']))->order('id desc')->find();
                            $finance_num_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $buy['userid']))->find();
                            $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $buy['userid']))->setField($rmb . 'd', 0);
                            $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $buy['userid']))->setInc($rmb, $you_user_buy[$rmb . 'd']);
                            //添加财务明细
                            $save_buy_rmb = $you_user_buy[$rmb . 'd'];
                            $finance_mum_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $buy['userid']))->find();
                            $finance_hash = md5($buy['userid'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.movesay.com');
                            $finance_num = ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']);
                            if ($finance_num > $finance['mum']) {
                                $finance_status = ($finance_num - $finance['mum']) > 1 ? 0 : 1;
                            } else {
                                $finance_status = ($finance['mum'] - $finance_num) > 1 ? 0 : 1;
                            }
                            $rs[] = $mo->table('movesay_finance')->add(array(
                                'userid'   => $buy['userid'],
                                'coinname' => 'cny',
                                'num_a'    => $finance_num_user_coin['cny'],
                                'num_b'    => $finance_num_user_coin['cnyd'],
                                'num'      => ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']),
                                'fee'      => $save_buy_rmb,
                                'type'     => 1,
                                'name'     => 'tradelog',
                                'nameid'   => $finance_nameid,
                                'remark'   => '交易中心-买家委托-解冻' . $market,
                                'mum_a'    => $finance_mum_user_coin['cny'],
                                'mum_b'    => $finance_mum_user_coin['cnyd'],
                                'mum'      => ($finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd']),
                                'move'     => $finance_hash,
                                'addtime'  => time(),
                                'status'   => $finance_status,
                            ));
                        }
                    }
                    // 卖家没有挂单了
                    if (!$you_sell) {
                        // 检查卖家余额
                        $you_user_sell = $mo->table('movesay_user_coin')->where(array('userid' => $sell['userid']))->find();
                        if ($you_user_sell[$xnb . 'd'] > 0) {
                            $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $sell['userid']))->setField($xnb . 'd', 0);
                            $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $sell['userid']))->setInc($rmb, $you_user_sell[$xnb . 'd']);
                        }
                    }
                    // 计算推荐奖励
                    // 得到买家财产
                    $invit_buy_user = $mo->table('movesay_user')->where(array('id' => $buy['userid']))->find();
                    // 得到卖家财产
                    $invit_sell_user = $mo->table('movesay_user')->where(array('id' => $sell['userid']))->find();
                    //
                    //
                    //
                    //
                    //
                    //
                    if ($invit_buy) {
                        // 一代奖励
                        if ($invit_1) {
                            // 买家上家计算
                            if ($buy_fee) {
                                if ($invit_buy_user['invit_1']) {
                                    $invit_buy_save_1 = round($buy_fee / 100 * $invit_1, 6);
                                    if ($invit_buy_save_1 > 0) {
                                        //先查询一次账号余额
                                        $finance = $mo->table('movesay_finance')->where(array('userid' => $invit_buy_user['invit_1']))->order('id desc')->find();
                                        $finance_num_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $invit_buy_user['invit_1']))->find();
                                        $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $invit_buy_user['invit_1']))->setInc($rmb, $invit_buy_save_1);
                                        $rs[] = $mo->table('movesay_invit')->add(array(
                                            'userid'  => $invit_buy_user['invit_1'],
                                            'invit'   => $buy['userid'],
                                            'name'    => '一代买入赠送',
                                            'type'    => $market . '买入交易赠送',
                                            'num'     => $amount,
                                            'mum'     => $mum,
                                            'fee'     => $invit_buy_save_1,
                                            'addtime' => time(),
                                            'status'  => 1,
                                        ));
                                        //添加财务明细
                                        $save_buy_rmb = $invit_buy_save_1;
                                        $finance_mum_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $invit_buy_user['invit_1']))->find();
                                        $finance_hash = md5($invit_buy_user['invit_1'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.movesay.com');
                                        $finance_num = ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']);
                                        if ($finance_num > $finance['mum']) {
                                            $finance_status = ($finance_num - $finance['mum']) > 1 ? 0 : 1;
                                        } else {
                                            $finance_status = ($finance['mum'] - $finance_num) > 1 ? 0 : 1;
                                        }
                                        $rs[] = $mo->table('movesay_finance')->add(array(
                                            'userid'   => $invit_buy_user['invit_1'],
                                            'coinname' => 'cny',
                                            'num_a'    => $finance_num_user_coin['cny'],
                                            'num_b'    => $finance_num_user_coin['cnyd'],
                                            'num'      => ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']),
                                            'fee'      => $save_buy_rmb,
                                            'type'     => 1,
                                            'name'     => 'tradelog',
                                            'nameid'   => $finance_nameid,
                                            'remark'   => '交易中心-交易一代赠送' . $market,
                                            'mum_a'    => $finance_mum_user_coin['cny'],
                                            'mum_b'    => $finance_mum_user_coin['cnyd'],
                                            'mum'      => ($finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd']),
                                            'move'     => $finance_hash,
                                            'addtime'  => time(),
                                            'status'   => $finance_status,
                                        ));
                                    }
                                }
                                if ($invit_buy_user['invit_2']) {
                                    $invit_buy_save_2 = round($buy_fee / 100 * $invit_2, 6);
                                    if ($invit_buy_save_2 > 0) {
                                        //先查询一次账号余额
                                        $finance = $mo->table('movesay_finance')->where(array('userid' => $invit_buy_user['invit_2']))->order('id desc')->find();
                                        $finance_num_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $invit_buy_user['invit_2']))->find();
                                        $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $invit_buy_user['invit_2']))->setInc($rmb, $invit_buy_save_2);
                                        $rs[] = $mo->table('movesay_invit')->add(array(
                                            'userid'  => $invit_buy_user['invit_2'],
                                            'invit'   => $buy['userid'],
                                            'name'    => '二代买入赠送',
                                            'type'    => $market . '买入交易赠送',
                                            'num'     => $amount,
                                            'mum'     => $mum,
                                            'fee'     => $invit_buy_save_2,
                                            'addtime' => time(),
                                            'status'  => 1,
                                        ));
                                        //添加财务明细
                                        $save_buy_rmb = $invit_buy_save_2;
                                        $finance_mum_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $invit_buy_user['invit_2']))->find();
                                        $finance_hash = md5($invit_buy_user['invit_2'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.movesay.com');
                                        $finance_num = ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']);
                                        if ($finance_num > $finance['mum']) {
                                            $finance_status = ($finance_num - $finance['mum']) > 1 ? 0 : 1;
                                        } else {
                                            $finance_status = ($finance['mum'] - $finance_num) > 1 ? 0 : 1;
                                        }
                                        $rs[] = $mo->table('movesay_finance')->add(array(
                                            'userid'   => $invit_buy_user['invit_2'],
                                            'coinname' => 'cny',
                                            'num_a'    => $finance_num_user_coin['cny'],
                                            'num_b'    => $finance_num_user_coin['cnyd'],
                                            'num'      => ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']),
                                            'fee'      => $save_buy_rmb,
                                            'type'     => 1,
                                            'name'     => 'tradelog',
                                            'nameid'   => $finance_nameid,
                                            'remark'   => '交易中心-交易二代赠送' . $market,
                                            'mum_a'    => $finance_mum_user_coin['cny'],
                                            'mum_b'    => $finance_mum_user_coin['cnyd'],
                                            'mum'      => ($finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd']),
                                            'move'     => $finance_hash,
                                            'addtime'  => time(),
                                            'status'   => $finance_status,
                                        ));
                                    }
                                }
                                if ($invit_buy_user['invit_3']) {
                                    $invit_buy_save_3 = round($buy_fee / 100 * $invit_3, 6);
                                    if ($invit_buy_save_3 > 0) {
                                        //先查询一次账号余额
                                        $finance = $mo->table('movesay_finance')->where(array('userid' => $invit_buy_user['invit_3']))->order('id desc')->find();
                                        $finance_num_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $invit_buy_user['invit_3']))->find();
                                        $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $invit_buy_user['invit_3']))->setInc($rmb, $invit_buy_save_3);
                                        $rs[] = $mo->table('movesay_invit')->add(array(
                                            'userid'  => $invit_buy_user['invit_3'],
                                            'invit'   => $buy['userid'],
                                            'name'    => '三代买入赠送',
                                            'type'    => $market . '买入交易赠送',
                                            'num'     => $amount,
                                            'mum'     => $mum,
                                            'fee'     => $invit_buy_save_3,
                                            'addtime' => time(),
                                            'status'  => 1,
                                        ));
                                        //添加财务明细
                                        $save_buy_rmb = $invit_buy_save_3;
                                        $finance_mum_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $invit_buy_user['invit_3']))->find();
                                        $finance_hash = md5($invit_buy_user['invit_3'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.movesay.com');
                                        $finance_num = ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']);
                                        if ($finance_num > $finance['mum']) {
                                            $finance_status = ($finance_num - $finance['mum']) > 1 ? 0 : 1;
                                        } else {
                                            $finance_status = ($finance['mum'] - $finance_num) > 1 ? 0 : 1;
                                        }
                                        $rs[] = $mo->table('movesay_finance')->add(array(
                                            'userid'   => $invit_buy_user['invit_3'],
                                            'coinname' => 'cny',
                                            'num_a'    => $finance_num_user_coin['cny'],
                                            'num_b'    => $finance_num_user_coin['cnyd'],
                                            'num'      => ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']),
                                            'fee'      => $save_buy_rmb,
                                            'type'     => 1,
                                            'name'     => 'tradelog',
                                            'nameid'   => $finance_nameid,
                                            'remark'   => '交易中心-交易三代赠送' . $market,
                                            'mum_a'    => $finance_mum_user_coin['cny'],
                                            'mum_b'    => $finance_mum_user_coin['cnyd'],
                                            'mum'      => ($finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd']),
                                            'move'     => $finance_hash,
                                            'addtime'  => time(),
                                            'status'   => $finance_status,
                                        ));
                                    }
                                }
                            }
                        }
                        if ($invit_sell) {
                            // 卖家计算
                            if ($sell_fee) {
                                if ($invit_sell_user['invit_1']) {
                                    $invit_sell_save_1 = round($sell_fee / 100 * $invit_1, 6);
                                    if ($invit_sell_save_1 > 0) {
                                        //先查询一次账号余额
                                        $finance = $mo->table('movesay_finance')->where(array('userid' => $invit_sell_user['invit_1']))->order('id desc')->find();
                                        $finance_num_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $invit_sell_user['invit_1']))->find();
                                        $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $invit_sell_user['invit_1']))->setInc($rmb, $invit_sell_save_1);
                                        $rs[] = $mo->table('movesay_user_coin')->getLastSql();
                                        $rs[] = $mo->table('movesay_invit')->add(array(
                                            'userid'  => $invit_sell_user['invit_1'],
                                            'invit'   => $sell['userid'],
                                            'name'    => '一代卖出赠送',
                                            'type'    => $market . '卖出交易赠送',
                                            'num'     => $amount,
                                            'mum'     => $mum,
                                            'fee'     => $invit_sell_save_1,
                                            'addtime' => time(),
                                            'status'  => 1,
                                        ));
                                        //添加财务明细
                                        $save_buy_rmb = $invit_sell_save_1;
                                        $finance_mum_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $invit_sell_user['invit_1']))->find();
                                        $finance_hash = md5($invit_sell_user['invit_1'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.movesay.com');
                                        $finance_num = ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']);
                                        if ($finance_num > $finance['mum']) {
                                            $finance_status = ($finance_num - $finance['mum']) > 1 ? 0 : 1;
                                        } else {
                                            $finance_status = ($finance['mum'] - $finance_num) > 1 ? 0 : 1;
                                        }
                                        $rs[] = $mo->table('movesay_finance')->add(array(
                                            'userid'   => $invit_sell_user['invit_1'],
                                            'coinname' => 'cny',
                                            'num_a'    => $finance_num_user_coin['cny'],
                                            'num_b'    => $finance_num_user_coin['cnyd'],
                                            'num'      => ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']),
                                            'fee'      => $save_buy_rmb,
                                            'type'     => 1,
                                            'name'     => 'tradelog',
                                            'nameid'   => $finance_nameid,
                                            'remark'   => '交易中心-交易一代代赠送' . $market,
                                            'mum_a'    => $finance_mum_user_coin['cny'],
                                            'mum_b'    => $finance_mum_user_coin['cnyd'],
                                            'mum'      => ($finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd']),
                                            'move'     => $finance_hash,
                                            'addtime'  => time(),
                                            'status'   => $finance_status,
                                        ));
                                    }
                                }
                                if ($invit_sell_user['invit_2']) {
                                    $invit_sell_save_2 = round($sell_fee / 100 * $invit_2, 6);
                                    if ($invit_sell_save_2 > 0) {
                                        //先查询一次账号余额
                                        $finance = $mo->table('movesay_finance')->where(array('userid' => $invit_sell_user['invit_2']))->order('id desc')->find();
                                        $finance_num_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $invit_sell_user['invit_2']))->find();
                                        $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $invit_sell_user['invit_2']))->setInc($rmb, $invit_sell_save_2);
                                        $rs[] = $mo->table('movesay_invit')->add(array(
                                            'userid'  => $invit_sell_user['invit_2'],
                                            'invit'   => $sell['userid'],
                                            'name'    => '二代卖出赠送',
                                            'type'    => $market . '卖出交易赠送',
                                            'num'     => $amount,
                                            'mum'     => $mum,
                                            'fee'     => $invit_sell_save_2,
                                            'addtime' => time(),
                                            'status'  => 1,
                                        ));
                                        //添加财务明细
                                        $save_buy_rmb = $invit_sell_save_2;
                                        $finance_mum_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $invit_sell_user['invit_2']))->find();
                                        $finance_hash = md5($invit_sell_user['invit_2'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.movesay.com');
                                        $finance_num = ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']);
                                        if ($finance_num > $finance['mum']) {
                                            $finance_status = ($finance_num - $finance['mum']) > 1 ? 0 : 1;
                                        } else {
                                            $finance_status = ($finance['mum'] - $finance_num) > 1 ? 0 : 1;
                                        }
                                        $rs[] = $mo->table('movesay_finance')->add(array(
                                            'userid'   => $invit_sell_user['invit_2'],
                                            'coinname' => 'cny',
                                            'num_a'    => $finance_num_user_coin['cny'],
                                            'num_b'    => $finance_num_user_coin['cnyd'],
                                            'num'      => ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']),
                                            'fee'      => $save_buy_rmb,
                                            'type'     => 1,
                                            'name'     => 'tradelog',
                                            'nameid'   => $finance_nameid,
                                            'remark'   => '交易中心-交易二代代赠送' . $market,
                                            'mum_a'    => $finance_mum_user_coin['cny'],
                                            'mum_b'    => $finance_mum_user_coin['cnyd'],
                                            'mum'      => ($finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd']),
                                            'move'     => $finance_hash,
                                            'addtime'  => time(),
                                            'status'   => $finance_status,
                                        ));
                                    }
                                }
                                if ($invit_sell_user['invit_3']) {
                                    $invit_sell_save_3 = round($sell_fee / 100 * $invit_3, 6);
                                    if ($invit_sell_save_3 > 0) {
                                        //先查询一次账号余额
                                        $finance = $mo->table('movesay_finance')->where(array('userid' => $invit_sell_user['invit_3']))->order('id desc')->find();
                                        $finance_num_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $invit_sell_user['invit_3']))->find();
                                        $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $invit_sell_user['invit_3']))->setInc($rmb, $invit_sell_save_3);
                                        $rs[] = $mo->table('movesay_invit')->add(array(
                                            'userid'  => $invit_sell_user['invit_3'],
                                            'invit'   => $sell['userid'],
                                            'name'    => '三代卖出赠送',
                                            'type'    => $market . '卖出交易赠送',
                                            'num'     => $amount,
                                            'mum'     => $mum,
                                            'fee'     => $invit_sell_save_3,
                                            'addtime' => time(),
                                            'status'  => 1,
                                        ));
                                        //添加财务明细
                                        $save_buy_rmb = $invit_sell_save_3;
                                        $finance_mum_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $invit_sell_user['invit_3']))->find();
                                        $finance_hash = md5($invit_sell_user['invit_3'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.movesay.com');
                                        $finance_num = ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']);
                                        if ($finance_num > $finance['mum']) {
                                            $finance_status = ($finance_num - $finance['mum']) > 1 ? 0 : 1;
                                        } else {
                                            $finance_status = ($finance['mum'] - $finance_num) > 1 ? 0 : 1;
                                        }
                                        $rs[] = $mo->table('movesay_finance')->add(array(
                                            'userid'   => $invit_sell_user['invit_3'],
                                            'coinname' => 'cny',
                                            'num_a'    => $finance_num_user_coin['cny'],
                                            'num_b'    => $finance_num_user_coin['cnyd'],
                                            'num'      => ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']),
                                            'fee'      => $save_buy_rmb,
                                            'type'     => 1,
                                            'name'     => 'tradelog',
                                            'nameid'   => $finance_nameid,
                                            'remark'   => '交易中心-交易三代代赠送' . $market,
                                            'mum_a'    => $finance_mum_user_coin['cny'],
                                            'mum_b'    => $finance_mum_user_coin['cnyd'],
                                            'mum'      => ($finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd']),
                                            'move'     => $finance_hash,
                                            'addtime'  => time(),
                                            'status'   => $finance_status,
                                        ));
                                    }
                                }
                            }
                        }
                    }
                    if (check_arr($rs)) {
                        $mo->execute('commit');
                        $mo->execute('unlock tables');
                        $new_trade_movesay = 1;
                        S('allsum', null);
                        S('getJsonTop' . $market, null);
                        S('getTradelog' . $market, null);
                        S('getDepth' . $market . '1', null);
                        S('getDepth' . $market . '3', null);
                        S('getDepth' . $market . '4', null);
                        S('ChartgetJsonData' . $market, null);
                        S('allcoin', null);
                        S('trends', null);
                    } else {
                        $mo->execute('rollback');
                        $mo->execute('unlock tables');
                        mlog('bb ' . implode("|", $rs));
                    }
                } else {
                    break;
                }
                unset($rs);
            }
            if ($new_trade_movesay) {
                // 最新成交价
                $new_price = round(M('TradeLog')->where(array(
                    'market' => $market,
                    'status' => 1,
                ))->order('id desc')->getField('price'), 6);
                // 最大买一价
                $buy_price = round(M('Trade')->where(array(
                    'type'   => 1,
                    'market' => $market,
                    'status' => 0,
                ))->max('price'), 6);
                // 最小卖一价
                $sell_price = round(M('Trade')->where(array(
                    'type'   => 2,
                    'market' => $market,
                    'status' => 0,
                ))->min('price'), 6);
                // 24H最低价
                $min_price = round(M('TradeLog')->where(array(
                    'market'  => $market,
                    'addtime' => array(
                        'gt',
                        (time() - (60 * 60 * 24)),
                    ),
                ))->min('price'), 6);
                // 24H最高价
                $max_price = round(M('TradeLog')->where(array(
                    'market'  => $market,
                    'addtime' => array(
                        'gt',
                        (time() - (60 * 60 * 24)),
                    ),
                ))->max('price'), 6);
                // 24H成交量
                $volume = round(M('TradeLog')->where(array(
                    'market'  => $market,
                    'addtime' => array(
                        'gt',
                        (time() - (60 * 60 * 24)),
                    ),
                ))->sum('num'), 6);
                // 24H开盘价
                $sta_price = round(M('TradeLog')->where(array(
                    'market'  => $market,
                    'status'  => 1,
                    'addtime' => array(
                        'gt',
                        (time() - (60 * 60 * 24)),
                    ),
                ))->order('id asc')->getField('price'), 6);
                $Cmarket = M('Market')->where(array('name' => $market))->find();
                if ($Cmarket['new_price'] != $new_price) {
                    $upCoinData['new_price'] = $new_price;
                }
                if ($Cmarket['buy_price'] != $buy_price) {
                    $upCoinData['buy_price'] = $buy_price;
                }
                if ($Cmarket['sell_price'] != $sell_price) {
                    $upCoinData['sell_price'] = $sell_price;
                }
                if ($Cmarket['min_price'] != $min_price) {
                    $upCoinData['min_price'] = $min_price;
                }
                if ($Cmarket['max_price'] != $max_price) {
                    $upCoinData['max_price'] = $max_price;
                }
                if ($Cmarket['volume'] != $volume) {
                    $upCoinData['volume'] = $volume;
                }
                $change = round(($new_price - $Cmarket['hou_price']) / $Cmarket['hou_price'] * 100, 2);
                //if ($change) {
                $upCoinData['change'] = $change;
                // }
                if ($upCoinData) {
                    M('Market')->where(array('name' => $market))->save($upCoinData);
                    M('Market')->execute('commit');
                    S('home_market', null);
                }
            }
        }

        //计算行情
        public function hangqing($market = null)
        {
            if (empty($market)) {
                return null;
            }
            // 间隔时间数组
            $timearr = array(
                1,
                3,
                5,
                10,
                15,
                30,
                60,
                120,
                240,
                360,
                720,
                1440,
                10080,
            );
            foreach ($timearr as $k => $v) {
                // 查询最后一个json记录
                $tradeJson = M('TradeJson')->where(array(
                    'market' => $market,
                    'type'   => $v,
                ))->order('id desc')->find();
                if ($tradeJson) {
                    $addtime = $tradeJson['addtime'];
                } else {
                    // 直接生成数据 取最开始的数据时间
                    $addtime = M('TradeLog')->where(array('market' => $market))->order('id asc')->getField('addtime');
                }
                if ($addtime) {
                    // 获取时间范围内的交易量
                    $youtradelog = M('TradeLog')->where('addtime >=' . $addtime . '  and market =\'' . $market . '\'')->sum('num');
                }
                if ($youtradelog) {
                    // $addtime=$addtime-(60*$v);
                    if ($v == 1) {
                        $start_time = $addtime;
                    } else {
                        $start_time = mktime(date('H', $addtime), (floor(date('i', $addtime) / $v) * $v), 0, date('m', $addtime), date('d', $addtime), date('Y', $addtime));
                    }
                    for ($x = 0; $x <= 20; $x++) {
                        // 数据开始时间
                        $na = $start_time + 60 * $v * $x;
                        // 数据结束时间
                        $nb = $start_time + 60 * $v * ($x + 1);
                        // 超过最新时间跳出循环
                        if ($na > time()) {
                            break;
                        }
                        // 获取时间范围内的交易量
                        $sum = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->sum('num');
                        // 有数据
                        if ($sum) {
                            // 获取时间范围内的开始价
                            $sta = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->order('id asc')->getField('price');
                            // 获取时间范围内的最高价
                            $max = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->max('price');
                            // 获取时间范围内的最低价
                            $min = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->min('price');
                            // 获取时间范围内的结束价
                            $end = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->order('id desc')->getField('price');
                            // 进行数组拼接
                            $d = array(
                                $na,
                                $sum,
                                $sta,
                                $max,
                                $min,
                                $end,
                            );
                            // 查询这个JSON记录是否存在
                            if (M('TradeJson')->where(array(
                                'market'  => $market,
                                'addtime' => $na,
                                'type'    => $v,
                            ))->find()
                            ) {
                                // 存在 就更新数据
                                M('TradeJson')->where(array(
                                    'market'  => $market,
                                    'addtime' => $na,
                                    'type'    => $v,
                                ))->save(array('data' => json_encode($d)));
                                M('TradeJson')->execute('commit');
                            } else {
                                // 不存在直接添加数据
                                M('TradeJson')->add(array(
                                    'market'  => $market,
                                    'data'    => json_encode($d),
                                    'addtime' => $na,
                                    'type'    => $v,
                                ));
                                M('TradeJson')->execute('commit');
                                M('TradeJson')->where(array(
                                    'market' => $market,
                                    'data'   => '',
                                    'type'   => $v,
                                ))->delete();
                                M('TradeJson')->execute('commit');
                            }
                        } else {
                            M('TradeJson')->add(array(
                                'market'  => $market,
                                'data'    => '',
                                'addtime' => $na,
                                'type'    => $v,
                            ));
                            M('TradeJson')->execute('commit');
                        }
                    }
                }
            }
        }

        //撤销委托
        public function chexiao($id = null)
        {
            if (!check($id, 'd')) {
                return array(
                    '0',
                    '参数错误',
                );
            }
            $trade = M('Trade')->where(array('id' => $id))->find();
            if (!$trade) {
                return array(
                    '0',
                    '订单不存在',
                );
            }
            if ($trade['status'] != 0) {
                return array(
                    '0',
                    '订单不能撤销',
                );
            }
            // 得到市场
            $xnb = explode("_", $trade['market'])[0];
            $rmb = explode("_", $trade['market'])[1];
            if (!$xnb) {
                return array(
                    '0',
                    '卖出市场错误',
                );
            }
            if (!$rmb) {
                return array(
                    '0',
                    '买入市场错误',
                );
            }
            // 手续费
            $fee_buy = C('market')[$trade['market']]['fee_buy'];
            $fee_sell = C('market')[$trade['market']]['fee_sell'];
            if ($fee_buy < 0) {
                return array(
                    '0',
                    '买入手续费错误',
                );
            }
            if ($fee_sell < 0) {
                return array(
                    '0',
                    '卖出手续费错误',
                );
            }
            // 冻结余额
            $user_coin = M('UserCoin')->where(array('userid' => $trade['userid']))->find();
            // 执行处理
            $mo = M();
            $mo->execute('set autocommit=0');
            $mo->execute('lock tables movesay_user_coin write  , movesay_trade write ,movesay_finance write');
            $rs = array();
            //账号余额
            $user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $trade['userid']))->find();
            if ($trade['type'] == 1) {
                // 得到撤单金额
                $mun = round(($trade['num'] - $trade['deal']) * $trade['price'] / 100 * (100 + $fee_buy), 8);
                // 检查买家余额
                $user_buy = $mo->table('movesay_user_coin')->where(array('userid' => $trade['userid']))->find();
                // 冻结金额可能有误差
                if (round($user_buy[$rmb . 'd'], 8) >= $mun) {
                    $save_buy_rmb = $mun;
                } else {
                    if ((round($user_buy[$rmb . 'd'], 8) + 1) >= $mun) {
                        // 误差1个币以内
                        $save_buy_rmb = $user_buy[$rmb . 'd'];
                    } else {
                        $mo->execute('rollback');
                        $mo->execute('unlock tables');
                        M('Trade')->where(array('id' => $id))->setField('status', 1);
                        return array(
                            '0',
                            '撤销失败',
                        );
                    }
                }
                //先查询一次账号余额
                $finance = $mo->table('movesay_finance')->where(array('userid' => $trade['userid']))->order('id desc')->find();
                $finance_num_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $trade['userid']))->find();
                // 增加正常人民币
                $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $trade['userid']))->setInc($rmb, $save_buy_rmb);
                // 减少冻结人民币
                $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $trade['userid']))->setDec($rmb . 'd', $save_buy_rmb);
                //添加财务明细
                $finance_nameid = $trade['id'];
                $save_buy_rmb = $save_buy_rmb;
                $finance_mum_user_coin = $mo->table('movesay_user_coin')->where(array('userid' => $trade['userid']))->find();
                $finance_hash = md5($trade['userid'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $save_buy_rmb . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.movesay.com');
                $finance_num = ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']);
                if ($finance_num > $finance['mum']) {
                    $finance_status = ($finance_num - $finance['mum']) > 1 ? 0 : 1;
                } else {
                    $finance_status = ($finance['mum'] - $finance_num) > 1 ? 0 : 1;
                }
                $rs[] = $mo->table('movesay_finance')->add(array(
                    'userid'   => $trade['userid'],
                    'coinname' => 'cny',
                    'num_a'    => $finance_num_user_coin['cny'],
                    'num_b'    => $finance_num_user_coin['cnyd'],
                    'num'      => ($finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd']),
                    'fee'      => $save_buy_rmb,
                    'type'     => 1,
                    'name'     => 'trade',
                    'nameid'   => $finance_nameid,
                    'remark'   => '交易中心-交易撤销' . $trade['market'],
                    'mum_a'    => $finance_mum_user_coin['cny'],
                    'mum_b'    => $finance_mum_user_coin['cnyd'],
                    'mum'      => ($finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd']),
                    'move'     => $finance_hash,
                    'addtime'  => time(),
                    'status'   => $finance_status,
                ));
                // 更新挂单状态
                $rs[] = $mo->table('movesay_trade')->where(array('id' => $trade['id']))->setField('status', 2);
                // 检查买家是否还有挂单
                $you_buy = $mo->table('movesay_trade')->where(array(
                    'market' => array(
                        'like',
                        '%' . $rmb . '%',
                    ),
                    'status' => 0,
                    'userid' => $trade['userid'],
                ))->find();
                // 买家没有挂单了
                if (!$you_buy) {
                    // 检查买家余额
                    $you_user_buy = $mo->table('movesay_user_coin')->where(array('userid' => $trade['userid']))->find();
                    if ($you_user_buy[$rmb . 'd'] > 0) {
                        $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $trade['userid']))->setField($rmb . 'd', 0);
                    }
                }
            } elseif ($trade['type'] == 2) {
                $mun = round($trade['num'] - $trade['deal'], 8);
                // 检查卖家余额
                $user_sell = $mo->table('movesay_user_coin')->where(array('userid' => $trade['userid']))->find();
                // 冻结金额可能有误差
                if (round($user_sell[$xnb . 'd'], 8) >= $mun) {
                    $save_sell_xnb = $mun;
                } else {
                    if ((round($user_sell[$xnb . 'd'], 8) + 1) >= $mun) {
                        // 误差1个币以内
                        $save_sell_xnb = $user_sell[$xnb . 'd'];
                    } else {
                        $mo->execute('rollback');
                        M('Trade')->where(array('id' => $trade['id']))->setField('status', 2);
                        $mo->execute('commit');
                        return array(
                            '0',
                            '撤销失败',
                        );
                    }
                }
                // 增加正常虚拟币
                $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $trade['userid']))->setInc($xnb, $save_sell_xnb);
                // 减少冻结虚拟币
                $rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $trade['userid']))->setDec($xnb . 'd', $save_sell_xnb);
                // 更新挂单状态
                $rs[] = $mo->table('movesay_trade')->where(array('id' => $trade['id']))->setField('status', 2);
                // 检查卖家是否还有挂单
                $you_sell = $mo->table('movesay_trade')->where(array(
                    'market' => array(
                        'like',
                        $xnb . '%',
                    ),
                    'status' => 0,
                    'userid' => $trade['userid'],
                ))->find();
                // 卖家没有挂单了
                if (!$you_sell) {
                    // 检查卖家余额
                    $you_user_sell = $mo->table('movesay_user_coin')->where(array('userid' => $trade['userid']))->find();
                    if ($you_user_sell[$xnb . 'd'] > 0) {
                        //$rs[] = $mo->table('movesay_user_coin')->where(array('userid' => $trade['userid']))->setField($xnb . 'd', 0);
                        $mo->table('movesay_user_coin')->where(array('userid' => $trade['userid']))->setField($xnb . 'd', 0);
                    }
                }
            } else {
                $mo->execute('rollback');
                return array(
                    '0',
                    '撤销失败',
                );
            }
            if (check_arr($rs)) {
                $mo->execute('commit');
                $mo->execute('unlock tables');
                return array(
                    '1',
                    '撤销成功',
                );
            } else {
                $mo->execute('rollback');
                return array(
                    '0',
                    '撤销失败',
                );
            }
        }
    }



