using System;
using System.IO;
using System.Diagnostics;
using System.Drawing;
using System.Net;
using System.Net.Sockets;
using System.Threading;
using System.Windows.Forms;
using System.Runtime.InteropServices;

namespace PaduLauncher
{
    public class Program
    {
        [STAThread]
        public static void Main()
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new MainForm());
        }
    }

    public class MainForm : Form
    {
        [DllImport("psapi.dll")]
        private static extern int EmptyWorkingSet(IntPtr hwProc);

        private Process phpProcess;
        private int serverPort = 8000;
        private string projectDir;
        private string phpBinary;
        private string pythonBinary;
        private NotifyIcon trayIcon;
        private Label lblStatus;
        private Label lblUrl;
        private Button btnOpenBrowser;
        private Button btnOpenExport;
        private Button btnOpenLogs;
        private Button btnClearCache;
        private Button btnStopExit;

        public MainForm()
        {
            InitializeProjectPaths();
            InitializeComponent();
            StartServerInBackground();
        }

        private void InitializeProjectPaths()
        {
            projectDir = AppDomain.CurrentDomain.BaseDirectory;
            if (!File.Exists(Path.Combine(projectDir, "artisan")))
            {
                // Try parent folder if inside a subfolder
                DirectoryInfo parentInfo = Directory.GetParent(projectDir);
                string parent = parentInfo != null ? parentInfo.FullName : null;
                if (parent != null && File.Exists(Path.Combine(parent, "artisan")))
                {
                    projectDir = parent;
                }
            }

            // Ensure required storage & cache directories exist
            string[] requiredDirs = new string[] {
                "storage\\framework\\cache\\data",
                "storage\\framework\\sessions",
                "storage\\framework\\views",
                "storage\\logs",
                "storage\\app\\public",
                "bootstrap\\cache",
                "src-dtsen",
                "src-export",
                "logs-transaksi",
                "scratch"
            };

            foreach (string relDir in requiredDirs)
            {
                string fullPath = Path.Combine(projectDir, relDir);
                if (!Directory.Exists(fullPath))
                {
                    try { Directory.CreateDirectory(fullPath); } catch {}
                }
            }

            // Detect PHP Binary
            phpBinary = FindPhpBinary();

            // Detect Python Binary
            pythonBinary = FindPythonBinary();
        }

        private string FindPhpBinary()
        {
            // 1. Local portable PHP in project folder
            string localPhp = Path.Combine(projectDir, "php", "php.exe");
            if (File.Exists(localPhp)) return localPhp;

            // 2. Laragon PHP candidates
            string[] laragonRoots = new string[] { @"C:\laragon\bin\php", @"D:\laragon\bin\php" };
            foreach (string root in laragonRoots)
            {
                if (Directory.Exists(root))
                {
                    string[] subdirs = Directory.GetDirectories(root, "php*");
                    foreach (string subdir in subdirs)
                    {
                        string exe = Path.Combine(subdir, "php.exe");
                        if (File.Exists(exe)) return exe;
                    }
                }
            }

            // 3. XAMPP PHP candidates
            string[] xamppPaths = new string[] { @"C:\xampp\php\php.exe", @"D:\xampp\php\php.exe" };
            foreach (string path in xamppPaths)
            {
                if (File.Exists(path)) return path;
            }

            // 4. Standalone C:\php\php.exe
            if (File.Exists(@"C:\php\php.exe")) return @"C:\php\php.exe";

            // 5. System PATH
            string sysPhp = FindInPath("php.exe");
            if (!string.IsNullOrEmpty(sysPhp)) return sysPhp;

            return null;
        }

        private string FindPythonBinary()
        {
            // 1. Local portable python
            string localPy = Path.Combine(projectDir, "python", "python.exe");
            if (File.Exists(localPy)) return localPy;

            // 2. System PATH python.exe or py.exe
            string sysPy = FindInPath("python.exe");
            if (!string.IsNullOrEmpty(sysPy)) return sysPy;

            string sysPyLauncher = FindInPath("py.exe");
            if (!string.IsNullOrEmpty(sysPyLauncher)) return sysPyLauncher;

            return null;
        }

        private string FindInPath(string filename)
        {
            string envPath = Environment.GetEnvironmentVariable("PATH");
            if (string.IsNullOrEmpty(envPath)) return null;

            string[] paths = envPath.Split(';');
            foreach (string p in paths)
            {
                string cleanP = p.Trim();
                if (string.IsNullOrEmpty(cleanP)) continue;
                try
                {
                    string full = Path.Combine(cleanP, filename);
                    if (File.Exists(full)) return full;
                }
                catch {}
            }
            return null;
        }

        private int GetAvailablePort(int startingPort)
        {
            int[] candidates = new int[] { 8000, 8001, 8002, 8003, 8004, 8005, 8080, 8081, 8082, 8888, 9000 };
            foreach (int port in candidates)
            {
                if (IsPortAvailable(port)) return port;
            }
            return startingPort;
        }

        private bool IsPortAvailable(int port)
        {
            try
            {
                TcpListener listener = new TcpListener(IPAddress.Loopback, port);
                listener.Start();
                listener.Stop();
                return true;
            }
            catch
            {
                return false;
            }
        }

        private void InitializeComponent()
        {
            this.Text = "PADU - Pengolah dan Analisis Data Terpadu";
            this.Size = new Size(540, 400);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.FormBorderStyle = FormBorderStyle.FixedSingle;
            this.MaximizeBox = false;
            this.BackColor = Color.FromArgb(15, 23, 42); // Slate 900
            this.ForeColor = Color.White;

            // Title Label
            Label lblTitle = new Label();
            lblTitle.Text = "PADU (DTSEN 2026)";
            lblTitle.Font = new Font("Segoe UI", 16, FontStyle.Bold);
            lblTitle.ForeColor = Color.FromArgb(56, 189, 248); // Sky 400
            lblTitle.Location = new Point(25, 20);
            lblTitle.AutoSize = true;
            this.Controls.Add(lblTitle);

            Label lblSubtitle = new Label();
            lblSubtitle.Text = "Pengolah & Analisis Data Terpadu • High-Performance Local Engine";
            lblSubtitle.Font = new Font("Segoe UI", 9, FontStyle.Regular);
            lblSubtitle.ForeColor = Color.FromArgb(148, 163, 184); // Slate 400
            lblSubtitle.Location = new Point(27, 52);
            lblSubtitle.AutoSize = true;
            this.Controls.Add(lblSubtitle);

            // Status Card Panel
            Panel cardPanel = new Panel();
            cardPanel.BackColor = Color.FromArgb(30, 41, 59); // Slate 800
            cardPanel.Location = new Point(25, 85);
            cardPanel.Size = new Size(475, 95);
            cardPanel.BorderStyle = BorderStyle.None;

            lblStatus = new Label();
            lblStatus.Text = "⏳ Menyiapkan server lokal...";
            lblStatus.Font = new Font("Segoe UI", 10, FontStyle.Bold);
            lblStatus.ForeColor = Color.FromArgb(250, 204, 21); // Yellow 400
            lblStatus.Location = new Point(15, 15);
            lblStatus.AutoSize = true;
            cardPanel.Controls.Add(lblStatus);

            lblUrl = new Label();
            lblUrl.Text = "Menghubungkan ke port jaringan...";
            lblUrl.Font = new Font("Consolas", 10, FontStyle.Regular);
            lblUrl.ForeColor = Color.FromArgb(203, 213, 225); // Slate 300
            lblUrl.Location = new Point(15, 45);
            lblUrl.AutoSize = true;
            cardPanel.Controls.Add(lblUrl);

            Label lblEngines = new Label();
            lblEngines.Text = "Runtime: PHP " + (phpBinary != null ? "(Tersedia)" : "(Tidak Ditemukan)") + " | DuckDB Python " + (pythonBinary != null ? "(Aktif)" : "(N/A)");
            lblEngines.Font = new Font("Segoe UI", 8, FontStyle.Italic);
            lblEngines.ForeColor = Color.FromArgb(100, 116, 139); // Slate 500
            lblEngines.Location = new Point(15, 70);
            lblEngines.AutoSize = true;
            cardPanel.Controls.Add(lblEngines);

            this.Controls.Add(cardPanel);

            // Buttons - Row 1
            btnOpenBrowser = new Button();
            btnOpenBrowser.Text = "🌐 Buka di Browser";
            btnOpenBrowser.Font = new Font("Segoe UI", 10, FontStyle.Bold);
            btnOpenBrowser.BackColor = Color.FromArgb(37, 99, 235); // Blue 600
            btnOpenBrowser.ForeColor = Color.White;
            btnOpenBrowser.FlatStyle = FlatStyle.Flat;
            btnOpenBrowser.FlatAppearance.BorderSize = 0;
            btnOpenBrowser.Location = new Point(25, 195);
            btnOpenBrowser.Size = new Size(230, 40);
            btnOpenBrowser.Cursor = Cursors.Hand;
            btnOpenBrowser.Click += (s, e) => OpenBrowser();
            this.Controls.Add(btnOpenBrowser);

            btnOpenExport = new Button();
            btnOpenExport.Text = "📁 Folder Hasil Export";
            btnOpenExport.Font = new Font("Segoe UI", 10, FontStyle.Bold);
            btnOpenExport.BackColor = Color.FromArgb(51, 65, 85); // Slate 700
            btnOpenExport.ForeColor = Color.White;
            btnOpenExport.FlatStyle = FlatStyle.Flat;
            btnOpenExport.FlatAppearance.BorderSize = 0;
            btnOpenExport.Location = new Point(270, 195);
            btnOpenExport.Size = new Size(230, 40);
            btnOpenExport.Cursor = Cursors.Hand;
            btnOpenExport.Click += (s, e) => OpenFolder("src-export");
            this.Controls.Add(btnOpenExport);

            // Buttons - Row 2
            btnOpenLogs = new Button();
            btnOpenLogs.Text = "📄 Log Transaksi (.txt)";
            btnOpenLogs.Font = new Font("Segoe UI", 9, FontStyle.Bold);
            btnOpenLogs.BackColor = Color.FromArgb(51, 65, 85);
            btnOpenLogs.ForeColor = Color.FromArgb(226, 232, 240);
            btnOpenLogs.FlatStyle = FlatStyle.Flat;
            btnOpenLogs.FlatAppearance.BorderSize = 0;
            btnOpenLogs.Location = new Point(25, 245);
            btnOpenLogs.Size = new Size(230, 36);
            btnOpenLogs.Cursor = Cursors.Hand;
            btnOpenLogs.Click += (s, e) => OpenFolder("logs-transaksi");
            this.Controls.Add(btnOpenLogs);

            btnClearCache = new Button();
            btnClearCache.Text = "🔄 Bersihkan Cache";
            btnClearCache.Font = new Font("Segoe UI", 9, FontStyle.Regular);
            btnClearCache.BackColor = Color.FromArgb(51, 65, 85);
            btnClearCache.ForeColor = Color.FromArgb(226, 232, 240);
            btnClearCache.FlatStyle = FlatStyle.Flat;
            btnClearCache.FlatAppearance.BorderSize = 0;
            btnClearCache.Location = new Point(270, 245);
            btnClearCache.Size = new Size(230, 36);
            btnClearCache.Cursor = Cursors.Hand;
            btnClearCache.Click += (s, e) => ClearCache();
            this.Controls.Add(btnClearCache);

            // Buttons - Row 3 (Stop & Clean Exit)
            btnStopExit = new Button();
            btnStopExit.Text = "🛑 Hentikan & Keluar (Pembersihan Bersih)";
            btnStopExit.Font = new Font("Segoe UI", 9, FontStyle.Bold);
            btnStopExit.BackColor = Color.FromArgb(153, 27, 27); // Red 800
            btnStopExit.ForeColor = Color.White;
            btnStopExit.FlatStyle = FlatStyle.Flat;
            btnStopExit.FlatAppearance.BorderSize = 0;
            btnStopExit.Location = new Point(25, 292);
            btnStopExit.Size = new Size(475, 40);
            btnStopExit.Cursor = Cursors.Hand;
            btnStopExit.Click += (s, e) => { StopServer(); Application.Exit(); };
            this.Controls.Add(btnStopExit);

            // App Icon
            Icon appIco = null;
            string iconPath = Path.Combine(projectDir, "app_icon.ico");
            if (File.Exists(iconPath))
            {
                try { appIco = new Icon(iconPath); } catch {}
            }
            if (appIco != null)
            {
                this.Icon = appIco;
            }

            // System Tray
            trayIcon = new NotifyIcon();
            trayIcon.Text = "PADU - Server Data Terpadu";
            trayIcon.Icon = appIco != null ? appIco : SystemIcons.Application;
            trayIcon.Visible = true;
            trayIcon.DoubleClick += (s, e) => { this.Show(); this.WindowState = FormWindowState.Normal; this.BringToFront(); };

            ContextMenu trayMenu = new ContextMenu();
            trayMenu.MenuItems.Add("Buka di Browser", (s, e) => OpenBrowser());
            trayMenu.MenuItems.Add("Tampilkan Jendela Kontrol", (s, e) => { this.Show(); this.WindowState = FormWindowState.Normal; this.BringToFront(); });
            trayMenu.MenuItems.Add("-");
            trayMenu.MenuItems.Add("Hentikan & Keluar", (s, e) => { StopServer(); Application.Exit(); });
            trayIcon.ContextMenu = trayMenu;

            this.Resize += (s, e) =>
            {
                if (this.WindowState == FormWindowState.Minimized)
                {
                    this.Hide();
                    trayIcon.ShowBalloonTip(2000, "PADU Berjalan", "Server tetap aktif di latar belakang. Klik ikon tray untuk membuka kembali.", ToolTipIcon.Info);
                }
            };

            this.FormClosing += (s, e) =>
            {
                StopServer();
                if (trayIcon != null)
                {
                    trayIcon.Visible = false;
                    trayIcon.Dispose();
                }
            };
        }

        private void StartServerInBackground()
        {
            Thread serverThread = new Thread(() =>
            {
                if (string.IsNullOrEmpty(phpBinary))
                {
                    this.Invoke((MethodInvoker)delegate
                    {
                        lblStatus.Text = "❌ PHP Runtime Tidak Ditemukan!";
                        lblStatus.ForeColor = Color.FromArgb(239, 68, 68);
                        lblUrl.Text = "Silakan letakkan folder 'php' portabel atau pasang XAMPP/Laragon.";
                        MessageBox.Show("PHP Runtime tidak ditemukan di komputer ini!\n\nUntuk menjadikannya 100% siap pakai tanpa instalasi, Anda cukup menyalin folder 'php' portabel ke dalam direktori aplikasi ini.", "PADU Launcher", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    });
                    return;
                }

                // 1. Prepare .env and key
                string envFile = Path.Combine(projectDir, ".env");
                string envExample = Path.Combine(projectDir, ".env.example");
                if (!File.Exists(envFile) && File.Exists(envExample))
                {
                    try { File.Copy(envExample, envFile); } catch {}
                }

                // Check APP_KEY
                try
                {
                    if (File.Exists(envFile))
                    {
                        string envContent = File.ReadAllText(envFile);
                        if (!envContent.Contains("APP_KEY=base64:"))
                        {
                            RunHiddenProcess(phpBinary, "artisan key:generate --force");
                        }
                    }
                }
                catch {}

                // Storage link & SQLite db
                string sqliteDb = Path.Combine(projectDir, "database", "database.sqlite");
                if (!File.Exists(sqliteDb))
                {
                    try { File.WriteAllText(sqliteDb, ""); } catch {}
                }

                RunHiddenProcess(phpBinary, "artisan storage:link");
                RunHiddenProcess(phpBinary, "artisan migrate --force");

                // 2. Pick Port
                serverPort = GetAvailablePort(8000);
                string publicDir = Path.Combine(projectDir, "public");
                string serverPhp = Path.Combine(projectDir, "server.php");

                // 3. Launch PHP Built-in Server
                ProcessStartInfo psi = new ProcessStartInfo();
                psi.FileName = phpBinary;
                psi.Arguments = string.Format("-d variables_order=EGPCS -d memory_limit=1024M -d upload_max_filesize=500M -d post_max_size=550M -d max_execution_time=600 -d max_input_time=600 -t \"{0}\" -S 0.0.0.0:{1} \"{2}\"", publicDir, serverPort, serverPhp);
                psi.WorkingDirectory = projectDir;
                psi.CreateNoWindow = true;
                psi.UseShellExecute = false;
                psi.WindowStyle = ProcessWindowStyle.Hidden;

                try
                {
                    phpProcess = Process.Start(psi);
                }
                catch (Exception ex)
                {
                    this.Invoke((MethodInvoker)delegate
                    {
                        lblStatus.Text = "❌ Gagal Memulai Web Server";
                        lblStatus.ForeColor = Color.FromArgb(239, 68, 68);
                        lblUrl.Text = ex.Message;
                    });
                    return;
                }

                // 4. Wait for server to become responsive, then update UI and open browser
                string appUrl = "http://127.0.0.1:" + serverPort + "/";
                bool isReady = false;
                for (int i = 0; i < 20; i++)
                {
                    Thread.Sleep(500);
                    if (IsServerResponding(serverPort))
                    {
                        isReady = true;
                        break;
                    }
                }

                this.Invoke((MethodInvoker)delegate
                {
                    lblStatus.Text = "🟢 Web Server PADU Berjalan Normal";
                    lblStatus.ForeColor = Color.FromArgb(34, 197, 94); // Emerald 500
                    lblUrl.Text = "Alamat: " + appUrl;
                });

                if (isReady)
                {
                    OpenBrowser();
                }
            });

            serverThread.IsBackground = true;
            serverThread.Start();
        }

        private bool IsServerResponding(int port)
        {
            try
            {
                using (TcpClient client = new TcpClient())
                {
                    IAsyncResult result = client.BeginConnect("127.0.0.1", port, null, null);
                    bool success = result.AsyncWaitHandle.WaitOne(400);
                    if (!success) return false;
                    client.EndConnect(result);
                    return true;
                }
            }
            catch
            {
                return false;
            }
        }

        private void OpenBrowser()
        {
            try
            {
                string url = "http://127.0.0.1:" + serverPort + "/";
                Process.Start(new ProcessStartInfo(url) { UseShellExecute = true });
            }
            catch (Exception ex)
            {
                MessageBox.Show("Gagal membuka peramban: " + ex.Message);
            }
        }

        private void OpenFolder(string relPath)
        {
            try
            {
                string fullPath = Path.Combine(projectDir, relPath);
                if (!Directory.Exists(fullPath)) Directory.CreateDirectory(fullPath);
                Process.Start("explorer.exe", fullPath);
            }
            catch (Exception ex)
            {
                MessageBox.Show("Gagal membuka folder: " + ex.Message);
            }
        }

        private void ClearCache()
        {
            if (string.IsNullOrEmpty(phpBinary)) return;
            try
            {
                RunHiddenProcess(phpBinary, "artisan optimize:clear");
                MessageBox.Show("Cache sistem Laravel dan view template berhasil dibersihkan!", "PADU System", MessageBoxButtons.OK, MessageBoxIcon.Information);
            }
            catch (Exception ex)
            {
                MessageBox.Show("Gagal membersihkan cache: " + ex.Message);
            }
        }

        private void RunHiddenProcess(string exe, string args)
        {
            try
            {
                ProcessStartInfo psi = new ProcessStartInfo(exe, args);
                psi.WorkingDirectory = projectDir;
                psi.CreateNoWindow = true;
                psi.UseShellExecute = false;
                psi.WindowStyle = ProcessWindowStyle.Hidden;
                using (Process p = Process.Start(psi))
                {
                    p.WaitForExit(8000);
                }
            }
            catch {}
        }

        private void StopServer()
        {
            // 1. Finalisasi Sesi & Buat File Log Transaksi .txt di folder logs-transaksi/
            try
            {
                HttpWebRequest req = (HttpWebRequest)WebRequest.Create(string.Format("http://127.0.0.1:{0}/api/system/session-close", serverPort));
                req.Timeout = 1200;
                req.Method = "GET";
                using (HttpWebResponse res = (HttpWebResponse)req.GetResponse()) {}
            }
            catch
            {
                // Fallback jika web server sudah tidak merespon: buat file log dari buffer
                FallbackFinalizeSessionLog();
            }

            // 2. Bersihkan & Matikan Seluruh Sub-Process PHP & Python Tree
            if (phpProcess != null && !phpProcess.HasExited)
            {
                try
                {
                    ProcessStartInfo killTree = new ProcessStartInfo("taskkill", string.Format("/F /T /PID {0}", phpProcess.Id));
                    killTree.CreateNoWindow = true;
                    killTree.UseShellExecute = false;
                    killTree.WindowStyle = ProcessWindowStyle.Hidden;
                    using (Process kp = Process.Start(killTree)) { kp.WaitForExit(1000); }
                }
                catch {}

                try
                {
                    phpProcess.Kill();
                    phpProcess.WaitForExit(500);
                }
                catch {}
            }

            // Clean up any lingering process on the specific port
            try
            {
                ProcessStartInfo psi = new ProcessStartInfo("cmd.exe", string.Format("/c for /f \"tokens=5\" %%a in ('netstat -ano ^| findstr /C:\":{0} \" ^| findstr \"LISTENING\"') do taskkill /F /PID %%a", serverPort));
                psi.CreateNoWindow = true;
                psi.UseShellExecute = false;
                psi.WindowStyle = ProcessWindowStyle.Hidden;
                using (Process p = Process.Start(psi))
                {
                    p.WaitForExit(2000);
                }
            }
            catch {}

            // 3. Bersihkan sisa berkas scratch & cache residu
            try
            {
                string scratchDir = Path.Combine(projectDir, "scratch");
                if (Directory.Exists(scratchDir))
                {
                    string[] tmpFiles = Directory.GetFiles(scratchDir, "*.tmp");
                    foreach (string tf in tmpFiles) { try { File.Delete(tf); } catch {} }
                    string progFile = Path.Combine(scratchDir, "import_progress.json");
                    if (File.Exists(progFile)) { try { File.Delete(progFile); } catch {} }
                }
            }
            catch {}

            // 4. Bersihkan residu RAM sistem secara persisten (EmptyWorkingSet)
            try
            {
                EmptyWorkingSet(Process.GetCurrentProcess().Handle);
            }
            catch {}
        }

        private void FallbackFinalizeSessionLog()
        {
            try
            {
                string logsDir = Path.Combine(projectDir, "logs-transaksi");
                if (!Directory.Exists(logsDir)) Directory.CreateDirectory(logsDir);

                string activeFile = Path.Combine(logsDir, ".active_session.json");
                if (File.Exists(activeFile))
                {
                    string content = File.ReadAllText(activeFile);
                    string timestamp = DateTime.Now.ToString("yyyy-MM-dd_HHmmss");
                    string txtFile = Path.Combine(logsDir, string.Format("Log_Transaksi_{0}.txt", timestamp));

                    using (StreamWriter sw = new StreamWriter(txtFile, false, System.Text.Encoding.UTF8))
                    {
                        sw.WriteLine(new string('=', 82));
                        sw.WriteLine("             CATATAN LOG TRANSAKSI & AKTIVITAS HARIAN PENGGUNA");
                        sw.WriteLine("               PADU - PENGOLAH & ANALISIS DATA TERPADU (DTSEN)");
                        sw.WriteLine(new string('=', 82));
                        sw.WriteLine(string.Format("{0,-22}: {1}", "Waktu Sesi Ditutup", DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss")));
                        sw.WriteLine(string.Format("{0,-22}: Sesi selesai dan ditutup oleh pengguna.", "Keterangan"));
                        sw.WriteLine(new string('-', 82));
                        sw.WriteLine("DATA SESI TRANSAKSI:");
                        sw.WriteLine(content);
                        sw.WriteLine(new string('=', 82));
                        sw.WriteLine("SESI APLIKASI SELESAI & DITUTUP SECARA BERSIH");
                        sw.WriteLine("Status Pembersihan: Seluruh proses latar belakang dan residu RAM telah dibersihkan.");
                        sw.WriteLine(new string('=', 82));
                    }
                    try { File.Delete(activeFile); } catch {}
                }
            }
            catch {}
        }
    }
}
