::コマンドを非表示
@ echo off

::カレントディレクトリを指定しFilesumを実行（実行後コマンドプロンプトが閉じるようにstartコマンドを使用）
start O:\01管理部\04システム開発課\FileSum\Filesum.exe %~dp0

::ネットワークドライブ（M:\）ではなくUNC（\\bunkaren13\CommonFiles\）で実行した場合は、UNCはサポートされない云々のメッセージが出ますが、とりあえず実行できます
::ネットワークドライブとして割り当てられていない場合は実行されません