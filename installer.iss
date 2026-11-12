; Script Inno Setup untuk PADU (DTSEN 2026)
; Gunakan Inno Setup Compiler (gratis di https://jrsoftware.org/isdl.php) untuk mengompilasi menjadi satu file installer Setup_PADU_2026.exe

[Setup]
AppId={{9F3B2C8D-84C1-4A59-83E3-B2A8D9F2C101}
AppName=PADU - Pengolah dan Analisis Data Terpadu
AppVersion=1.0.2
AppPublisher=DTSEN 2026
DefaultDirName={autopf}\PADU_DTSEN
DefaultGroupName=PADU
OutputDir=dist
OutputBaseFilename=Setup_PADU_2026
SetupIconFile=app_icon.ico
Compression=lzma2/ultra64
SolidCompression=yes
WizardStyle=modern
DisableDirPage=no
PrivilegesRequired=lowest

[Languages]
Name: "indonesian"; MessagesFile: "compiler:Languages\Indonesian.isl"

[Tasks]
Name: "desktopicon"; Description: "Buat pintasan di Desktop (Desktop Shortcut)"; GroupDescription: "Pintasan Tambahan:"

[Files]
Source: "*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs; Excludes: ".git\*,.github\*,dist\*,storage\logs\*.log,*.tmp"

[Icons]
Name: "{group}\PADU (DTSEN 2026)"; Filename: "{app}\PADU.exe"; IconFilename: "{app}\app_icon.ico"
Name: "{group}\Hapus Instalasi PADU"; Filename: "{uninstallexe}"
Name: "{autodesktop}\PADU (DTSEN 2026)"; Filename: "{app}\PADU.exe"; IconFilename: "{app}\app_icon.ico"; Tasks: desktopicon

[Run]
Filename: "{app}\PADU.exe"; Description: "Jalankan PADU sekarang"; Flags: nowait postinstall skipifsilent

