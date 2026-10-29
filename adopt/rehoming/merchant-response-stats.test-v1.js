const R = require('./merchant-response-stats-v1.js');
const NOW = Date.parse('2026-10-04T12:00:00Z');
const H = 3600000;
const at = (hoursAgo) => new Date(NOW - hoursAgo * H).toISOString();
let pass = 0, fail = 0;
function eq(name, got, want) {
  const ok = JSON.stringify(got) === JSON.stringify(want);
  ok ? pass++ : fail++;
  console.log((ok ? 'PASS ' : 'FAIL ') + name + (ok ? '' : '\n   got  ' + JSON.stringify(got) + '\n   want ' + JSON.stringify(want)));
}
const inb = (thread, hoursAgo) => ({ thread_id: thread, from_type: 'user', created_at: at(hoursAgo) });
const out = (thread, hoursAgo, type = 'admin') => ({ thread_id: thread, from_type: type, created_at: at(hoursAgo) });

// 1. fast merchant: replies in 1h, 2h, 3h
let m = [inb(1,100),out(1,99), inb(2,80),out(2,78), inb(3,60),out(3,57)];
eq('fast merchant', R.computeMerchantResponseStats(m, NOW), { sample_size:3, avg_reply_hours:2, response_rate:1, window_hours:48, tier:'few_hours' });

// 2. mixed: 1h, 30h, and one overdue-unanswered -> pct 2/3, tier uses 48h penalty => (1+30+48)/3=26.3 -> slower
m = [inb(1,200),out(1,199), inb(2,150),out(2,120), inb(3,100)];
eq('mixed with ignored enquiry', R.computeMerchantResponseStats(m, NOW), { sample_size:3, avg_reply_hours:15.5, response_rate:0.67, window_hours:48, tier:'slower' });

// 3. burst of 3 visitor messages before one reply = ONE enquiry (+ 2 more fast ones to reach MIN_SAMPLE)
m = [inb(1,100),inb(1,99.9),inb(1,99.8),out(1,99), inb(2,80),out(2,79), inb(3,60),out(3,59)];
eq('burst counts once', R.computeMerchantResponseStats(m, NOW).sample_size, 3);

// 4. bot/system replies must NOT count as a human reply (unanswered + overdue)
m = [inb(1,100),out(1,100,'bot'),out(1,99.9,'system'), inb(2,90),out(2,90,'bot'), inb(3,80),out(3,80,'bot')];
const r4 = R.computeMerchantResponseStats(m, NOW);
eq('bot/system replies ignored', [r4.avg_reply_hours, r4.response_rate, r4.tier], [null, 0, 'slower']);

// 5. soft-deleted messages ignored
m = [inb(1,100),{...out(1,99), deleted_at:'2026-10-01 10:00:00'}, inb(2,80),out(2,79), inb(3,60),out(3,59)];
const r5 = R.computeMerchantResponseStats(m, NOW);
eq('deleted reply ignored (enquiry 1 unanswered)', r5.response_rate, 0.67);

// 6. fresh unanswered enquiry (2h old) is NOT held against the merchant
m = [inb(1,100),out(1,99), inb(2,80),out(2,79), inb(3,60),out(3,59), inb(4,2)];
eq('fresh unanswered not counted', R.computeMerchantResponseStats(m, NOW).sample_size, 3);

// 7. below MIN_SAMPLE -> numbers but no tier/label
m = [inb(1,100),out(1,99), inb(2,80),out(2,79)];
const r7 = R.computeMerchantResponseStats(m, NOW);
eq('too few enquiries -> tier AND response_rate null (never a misleading 100%)', [r7.sample_size, r7.response_rate, r7.tier, R.tierLabel(r7.tier)], [2, null, null, null]);

// 8. outside rolling window excluded
m = [inb(1,24*95),out(1,24*95-1), inb(2,24*100),out(2,24*100-1), inb(3,24*120),out(3,24*120-1)];
eq('outside 90-day window excluded', R.computeMerchantResponseStats(m, NOW).sample_size, 0);

// 9. MAX_SAMPLES cap keeps the most recent
m = []; for (let i = 0; i < 60; i++) { m.push(inb(i, 1000 - i*10)); m.push(out(i, 999 - i*10)); }
eq('capped at 50', R.computeMerchantResponseStats(m, NOW, { WINDOW_DAYS: 9999 }).sample_size, 50);

// 10. empty / junk input never throws
eq('empty', R.computeMerchantResponseStats([], NOW), { sample_size:0, avg_reply_hours:null, response_rate:null, window_hours:48, tier:null });
eq('junk rows', R.computeMerchantResponseStats([null, {}, {thread_id:1,from_type:'user',created_at:'garbage'}], NOW).sample_size, 0);

// 11. MySQL datetime string format
const sql = (hoursAgo) => new Date(NOW - hoursAgo*H).toISOString().slice(0,19).replace('T',' ');
m = [ {thread_id:1,from_type:'user',created_at:sql(100)}, {thread_id:1,from_type:'admin',created_at:sql(99)},
      {thread_id:2,from_type:'user',created_at:sql(80)},  {thread_id:2,from_type:'admin',created_at:sql(79)},
      {thread_id:3,from_type:'user',created_at:sql(60)},  {thread_id:3,from_type:'admin',created_at:sql(59)} ];
eq('MySQL datetime strings parse', R.computeMerchantResponseStats(m, NOW).tier, 'few_hours');

// 12. day-tier boundary: replies ~10h -> within a day
m = [inb(1,100),out(1,90), inb(2,80),out(2,70), inb(3,60),out(3,50)];
eq('10h avg -> within a day', R.computeMerchantResponseStats(m, NOW).tier, 'within_day');

// 13. UNIT GUARD: response_rate must be a 0-1 fraction (B3 compares it to 0.80). A percentage here would make everyone Trusted.
m = [inb(1,100),out(1,99), inb(2,80),out(2,79), inb(3,60),out(3,59), inb(4,50),out(4,49)];
const r13 = R.computeMerchantResponseStats(m, NOW);
eq('response_rate is a 0-1 fraction usable against B3 threshold 0.80', [r13.response_rate <= 1, r13.response_rate >= 0.80], [true, true]);
const r13b = R.computeMerchantResponseStats([inb(1,200),out(1,199), inb(2,150), inb(3,100), inb(4,90)], NOW);
eq('1 of 4 answered -> 0.25, below B3 threshold', [r13b.response_rate, r13b.response_rate >= 0.80], [0.25, false]);

// 14. SHARED FIXTURES. The PHP port (merchant-response-stats.php) is verified against the exact same
//     cases, so the two implementations cannot drift apart. `node ... --write-fixtures` regenerates the file.
const fs = require('fs'), path = require('path');
const fixtureFile = path.join(__dirname, 'merchant-response-stats.fixtures.json');
const CASES = [
  ['fast merchant', [inb(1,100),out(1,99), inb(2,80),out(2,78), inb(3,60),out(3,57)]],
  ['mixed with ignored enquiry', [inb(1,200),out(1,199), inb(2,150),out(2,120), inb(3,100)]],
  ['burst counts once', [inb(1,100),inb(1,99.9),inb(1,99.8),out(1,99), inb(2,80),out(2,79), inb(3,60),out(3,59)]],
  ['bot and system replies ignored', [inb(1,100),out(1,100,'bot'),out(1,99.9,'system'), inb(2,90),out(2,90,'bot'), inb(3,80),out(3,80,'bot')]],
  ['deleted reply ignored', [inb(1,100),{...out(1,99), deleted_at:'2026-10-01 10:00:00'}, inb(2,80),out(2,79), inb(3,60),out(3,59)]],
  ['fresh unanswered not counted', [inb(1,100),out(1,99), inb(2,80),out(2,79), inb(3,60),out(3,59), inb(4,2)]],
  ['too few enquiries', [inb(1,100),out(1,99), inb(2,80),out(2,79)]],
  ['outside window', [inb(1,24*95),out(1,24*95-1), inb(2,24*100),out(2,24*100-1), inb(3,24*120),out(3,24*120-1)]],
  ['one of four answered', [inb(1,200),out(1,199), inb(2,150), inb(3,100), inb(4,90)]],
  ['day tier', [inb(1,100),out(1,90), inb(2,80),out(2,70), inb(3,60),out(3,50)]],
  ['junk rows', [null, {}, {thread_id:1,from_type:'user',created_at:'garbage'}]],
  ['same-timestamp reply keeps input order', [inb(1,100),out(1,100), inb(2,80),out(2,80), inb(3,60),out(3,60)]],
  ['mysql datetimes', [100,99,80,79,60,59].map((h,i)=>({thread_id: Math.floor(i/2)+1, from_type: i%2===0?'user':'admin', created_at: sql(h)}))],
  ['timezone-less string equals UTC', [{thread_id:1,from_type:'user',created_at:'2026-10-01 00:00:00'},{thread_id:1,from_type:'admin',created_at:'2026-10-01T02:00:00Z'},
                                       {thread_id:2,from_type:'user',created_at:'2026-10-02 00:00:00'},{thread_id:2,from_type:'admin',created_at:'2026-10-02T02:00:00Z'},
                                       {thread_id:3,from_type:'user',created_at:'2026-10-03 00:00:00'},{thread_id:3,from_type:'admin',created_at:'2026-10-03T02:00:00Z'}]]
];
const fixtures = { now: new Date(NOW).toISOString(), cases: CASES.map(([name, messages]) => ({
  name, messages, expected: R.computeMerchantResponseStats(messages, NOW) })) };
if (process.argv.includes('--write-fixtures')) { fs.writeFileSync(fixtureFile, JSON.stringify(fixtures, null, 2)); console.log('fixtures written'); }
const onDisk = JSON.parse(fs.readFileSync(fixtureFile, 'utf8'));
eq('fixtures file matches current JS output (' + onDisk.cases.length + ' cases)', onDisk, JSON.parse(JSON.stringify(fixtures)));
const tz = onDisk.cases.find(c => c.name === 'timezone-less string equals UTC').expected;
eq('timezone-less strings treated as UTC (2h replies -> few_hours, rate 1)', [tz.tier, tz.response_rate], ['few_hours', 1]);

console.log(`\n${pass} passed, ${fail} failed`);
process.exit(fail ? 1 : 0);
