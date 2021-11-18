import {Selector, RequestMock} from 'testcafe';

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
