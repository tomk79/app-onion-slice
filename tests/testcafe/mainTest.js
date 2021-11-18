import {Selector, Role} from 'testcafe';

// user: admin
const userAdmin = Role('http://127.0.0.1:8088/onion-slice.php', async t => {
    await t
        .typeText('input[name=login_id]', 'admin')
        .typeText('input[name=login_pw]', 'admin')
        .wait(500)
        .click('button[type=submit]');
});



fixture('OnionSlice UI Test')
  .page('http://127.0.0.1:8088/onion-slice.php');

test('h1', async t => {
  const $h1 = await Selector('h1');
  await t
    .wait(3000)
    .expect($h1.exists).ok()
    .expect($h1.count).eql(1)
    .expect($h1.innerText).eql('onion-slice');
});

test('Login', async t => {
  await t
    .useRole(userAdmin)
    .wait(500)
    .takeScreenshot('login-after.png');

  await t
    .expect((await Selector('h1')).innerText).eql('Home');
});

test('Initialize', async t => {
  // スタート画面
  await t
    .useRole(userAdmin)
    .wait(500)
    .click(await Selector('button[type=submit]'))
    .wait(500)
    .takeScreenshot('initialize-001.png');

  // composer install
  await t
    .useRole(userAdmin)
    .wait(500)
    .click(await Selector('button[type=submit]'))
    .wait(20000)
    .takeScreenshot('initialize-002.png');

  // Gitの初期化
  await t
    .useRole(userAdmin)
    .wait(500)
    .click(await Selector('button[type=submit]'))
    .wait(500)
    .takeScreenshot('initialize-003.png');
});
