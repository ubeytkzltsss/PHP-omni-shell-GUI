<div align="center">

## ⚠️ Educational use / Eğitim amaçlı kullanım

# php-omni-shell

**Single-file PHP web shell with GUI — file manager, terminal, privesc helpers, DB extraction.**
**Arayüzlü tek dosya PHP web shell — dosya yöneticisi, terminal, yetki yükseltme, veritabanı çekme.**

Windows · Linux · TR / EN · No disk writes · Self-deleting

</div>

---

> **EN** — Drop one `.php` file on a host, log in, and get a full GUI: browse, edit, download, upload, run commands, hunt for privilege escalation, dump databases. Nothing is written to disk, nothing is left behind.
>
> **TR** — Tek bir `.php` dosyasını sunucuya at, giriş yap, tam arayüz: gez, düzenle, indir, yükle, komut çalıştır, yetki yükseltme ara, veritabanı dök. Diske hiçbir şey yazılmaz, iz kalmaz.

---

## ⚠️ Disclaimer / Uyarı

**EN** — This project is published **for educational and authorized-testing purposes only**. It is a post-exploitation tool: it is meant to be uploaded to a server you own or are permitted to test, and removed afterwards.

**TR** — Bu proje **yalnızca eğitim ve yetkili test amacıyla** yayınlanmıştır. Bir sızma sonrası aracıdır: sahibi olduğun ya da test iznin olan bir sunucuya yüklenip iş bitince silinmek üzere tasarlanmıştır.

---

## Features / Özellikler

### File manager / Dosya yöneticisi
| EN | TR |
|---|---|
| Browse, breadcrumb, parent navigation | Gezinme, yol izi, üst dizin |
| Download, inline view, raw open (images/PDF) | İndirme, görüntüleme, ham açma |
| Edit and save in place | Yerinde düzenleme ve kaydetme |
| Upload (multi), rename, copy, move, delete | Çoklu yükleme, ad değiştir, kopyala, taşı, sil |
| Create dir / file, touch, chmod, chown, ACL | Dizin/dosya aç, touch, chmod, chown, ACL |
| Hash (md5/sha1/sha256), hex viewer | Hash, hex görüntüleyici |
| Recursive search, tree view | Özyinelemeli arama, ağaç görünümü |
| Streaming ZIP of a directory | Dizini akış halinde ZIP'leme |
| Safe-mode lock (blocks writes) | Kilit modu (yazmayı engeller) |

### Terminal
| EN | TR |
|---|---|
| Command execution with output | Çıktılı komut çalıştırma |
| Shell selection | Kabuk seçimi |
| User / elevated execution | Kullanıcı / yükseltilmiş çalıştırma |
| Automatic exec-function fallback chain | Otomatik exec fonksiyon zinciri |

### Privilege escalation
| EN | TR |
|---|---|
| Token / identity / group enumeration | Token / kimlik / grup sayımı |
| SUID + GTFOBins matching with ready commands | SUID + GTFOBins eşleşmesi, hazır komutlar |
| `sudo -n` detection and auto-elevation | `sudo -n` tespiti ve otomatik yükseltme |
| Capabilities, cron writability, group audit | Capabilities, cron yazılabilirliği, grup denetimi |
| `SeImpersonate` + PrintSpoofer automation | `SeImpersonate` + PrintSpoofer otomasyonu |
| linpeas download / run / clean | linpeas indir / çalıştır / sil |
| `/etc/shadow`, `/etc/passwd` reading | `/etc/shadow`, `/etc/passwd` okuma |
| AlwaysInstallElevated, unquoted service paths | AlwaysInstallElevated, tırnaklanmamış servis yolları |

### Database
| EN | TR |
|---|---|
| Credential hunting in site configs | Site config'lerinde kimlik avı |
| Plesk admin credential retrieval | Plesk admin kimliği edinme |
| Working-credential detection | Çalışan kimlik tespiti |
| Per-DB `.sql` dump, `--all-databases` | DB başına `.sql`, tümü tek dosya |
| Raw data-directory ZIP fallback | Ham veri dizini ZIP yedeği |
| Ad-hoc SQL query runner | Serbest SQL sorgu çalıştırıcı |

### Server & tools
| EN | TR |
|---|---|
| System info, users, services, processes | Sistem bilgisi, kullanıcılar, servisler, süreçler |
| Network, connections, ARP, shares | Ağ, bağlantılar, ARP, paylaşımlar |
| Scheduled tasks, installed software, hosts | Zamanlanmış işler, kurulu yazılım, hosts |
| base64 encode/decode, reverse-shell generator | base64 kodla/çöz, reverse shell üretici |
| php.ini audit, self-delete | php.ini denetimi, kendini silme |
| TR / EN interface | TR / EN arayüz |

---

## Requirements / Gereksinimler

**EN**
- PHP 5.4+ (7.x / 8.x recommended)
- One of: `popen`, `shell_exec`, `system`, `exec`, `proc_open` for command features
- `hash` + `zlib` for ZIP (falls back to uncompressed if zlib is missing)
- No `ZipArchive`, no `exec` binary, no composer required

**TR**
- PHP 5.4+ (7.x / 8.x önerilir)
- Komut özellikleri için şunlardan biri: `popen`, `shell_exec`, `system`, `exec`, `proc_open`
- ZIP için `hash` + `zlib` (zlib yoksa sıkıştırmasız çalışır)
- `ZipArchive`, harici binary veya composer gerekmez

---

## Install / Kurulum

**EN**
1. Pick the file for your platform: `omni-win.php` or `omni-nix.php`
2. Rename it to something inconspicuous (`x.php`, `cron.php` …)
3. Edit `define('PASS','venice123');`
4. Upload over FTP / panel file manager
5. Open it in a browser and log in

**TR**
1. Platformuna uygun dosyayı seç: `omni-win.php` veya `omni-nix.php`
2. Dikkat çekmeyen bir ad ver (`x.php`, `cron.php` …)
3. `define('PASS','venice123');` satırını değiştir
4. FTP / panel dosya yöneticisiyle yükle
5. Tarayıcıdan aç ve giriş yap

Optional: `?k=yourpassword` logs in without a cookie. `?self=1` deletes the file after the operation.
İsteğe bağlı: `?k=sifren` cookie olmadan giriş yapar. `?self=1` işlem sonunda dosyayı siler.

---

## Usage / Kullanım

**EN** — Tabs on top: **files**, **terminal**, **root/SYSTEM**, **db**, **server**, **tools**. The lock button freezes all write operations so you can browse safely. Files named in a row download on click; the eye icon opens the editor.
**TR** — Üstteki sekmeler: **dosyalar**, **terminal**, **root/SYSTEM**, **db**, **sunucu**, **araçlar**. Kilit butonu tüm yazma işlemlerini dondurur, güvenle gezersin. Satırdaki dosya adı tıklanınca indirir, göz ikonu editörü açar.

---

## Privilege escalation / Yetki yükseltme

**EN**
- **Linux:** scan first. If `sudo -n` works, every write/delete/chmod already runs as root. If a SUID binary matches GTFOBins, one click gives a root shell. Writable `/etc/cron.d` gives a one-minute timer.
- **Windows:** scan first. `SeImpersonatePrivilege` present → `get exe` + `SYSTEM test`. Absent → check `AlwaysInstallElevated`, unquoted service paths, stored credentials.

**TR**
- **Linux:** önce tara. `sudo -n` çalışıyorsa tüm yazma/silme/chmod zaten root olarak koşar. Bir SUID ikili GTFOBins'e uyuyorsa tek tıkla root shell. `/etc/cron.d` yazılabilirse bir dakikalık zamanlayıcı.
- **Windows:** önce tara. `SeImpersonatePrivilege` varsa `exe indir` + `SYSTEM testi`. Yoksa `AlwaysInstallElevated`, tırnaklanmamış servis yolları, kayıtlı kimlikler.

---

## Database extraction / Veritabanı çekme

**EN** — The db tab runs a diagnostic first: it locates `mysqldump`/`mysql`, harvests credentials from site configs, tries the Plesk admin password source, then tests each credential. The first working one is used. If dumps fail, a raw ZIP of the data directory is offered.
**TR** — db sekmesi önce teşhis koşar: `mysqldump`/`mysql` bulur, site config'lerinden kimlik toplar, Plesk admin şifre kaynağını dener, her kimliği test eder. İlk çalışan kullanılır. Döküm başarısızsa ham veri dizini ZIP'i sunulur.

**Nothing is written to disk during a dump — output is streamed.**
**Döküm sırasında diske hiçbir şey yazılmaz — çıktı akıştırılır.**

---

## Security notes / Güvenlik notları

**EN**
- Change `PASS` before uploading. The default is public knowledge.
- Cookie auth is a static MD5 of the password — fine for short engagements, not for permanent hosting.
- Delete the file after use (`tools → self delete`) or rename it.
- PrintSpoofer / linpeas are written to the temp directory; both have a delete button.
- Use over HTTPS. Without it, the password travels in clear.

**TR**
- Yüklemeden önce `PASS` değiştir. Varsayılan herkesçe bilinir.
- Cookie kimliği şifrenin statik MD5'idir — kısa işler için uygun, kalıcı barındırma için değil.
- İş bitince dosyayı sil (`araçlar → kendini sil`) veya adını değiştir.
- PrintSpoofer / linpeas geçici dizine yazılır; ikisinin de silme butonu var.
- HTTPS üzerinden kullan. Yoksa şifre açık gider.

---

## Known limitations / Bilinen sınırlar

**EN**
- Both `popen` and `shell_exec` disabled → only the file manager works
- `open_basedir` confines the file manager; the terminal is not affected by it
- Raw `.ibd` copies can be inconsistent on a live server — prefer `.sql`
- PrintSpoofer needs `SeImpersonatePrivilege` and outbound internet
- Very large dumps: no `Content-Length`, the browser will not show a size

**TR**
- `popen` ve `shell_exec` ikisi de kapalıysa sadece dosya yöneticisi çalışır
- `open_basedir` dosya yöneticisini sınırlar; terminal etkilenmez
- Ham `.ibd` kopyaları canlı sunucuda tutarsız olabilir — `.sql` tercih et
- PrintSpoofer `SeImpersonatePrivilege` ve dışa internet ister
- Çok büyük dökümlerde `Content-Length` yok, tarayıcı boyut göstermez

---

## FAQ / SSS

**Does it work on shared hosting? / Paylaşımlı hostingde çalışır mı?**
**EN** — File manager yes. Terminal and DB need at least one exec function.
**TR** — Dosya yöneticisi evet. Terminal ve DB için en az bir exec fonksiyonu gerekir.

**Why read-only directories? / Neden salt-okunur dizinler?**
**EN** — The PHP pool user usually is not the account owner. Use the permission panel, or run through an elevated path if one exists.
**TR** — PHP havuz kullanıcısı genelde hesap sahibi değildir. İzin panelini kullan veya yükseltilmiş bir yol varsa ondan geç.

**Can it be detected? / Yakalanır mı?**
**EN** — Yes. It is a plain PHP file; a WAF may block `shell_exec` output. The base64 POST parameter transport reduces signature hits but does not eliminate them.
**TR** — Evet. Düz bir PHP dosyasıdır; WAF `shell_exec` çıktısını kesebilir. base64 POST taşıma imza isabetini azaltır ama sıfırlamaz.

**Why no framework? / Neden framework yok?**
**EN** — One file, zero dependencies, works on a locked-down host.
**TR** — Tek dosya, sıfır bağımlılık, kilitli sunucuda da çalışır.

---

## File structure / Dosya yapısı

```
omni-win.php    Windows — cmd/PowerShell, SeImpersonate, Plesk Windows
omni-nix.php    Linux  — sh/bash, SUID/GTFOBins, cron, Plesk Linux
```
---

<div align="center"><sub>Built for people who have FTP and five minutes.</sub></div>


<p align="center">
<img width="100" height="99" alt="resim" src="https://github.com/user-attachments/assets/7e14465d-bdef-421e-bc16-199a8646792e" />
</p>

<p align="center">
  Developed by <b>Ubeyt</b>.
</p>
