-- Aktifkan event scheduler MySQL sekali saja:
SET GLOBAL event_scheduler = ON;

DROP EVENT IF EXISTS ev_nonaktif_tunggakan;
CREATE EVENT ev_nonaktif_tunggakan
ON SCHEDULE EVERY 1 DAY
DO
  UPDATE pelanggan p
  JOIN kontrak_kredit k ON k.id_pelanggan = p.id_pelanggan AND k.status IN ('AKTIF','TUNDA')
  JOIN jadwal_angsuran j ON j.id_kontrak = k.id_kontrak AND j.status IN ('DUE','LATE')
  LEFT JOIN (
    SELECT id_kontrak,
           MAX(GREATEST(DATEDIFF(CURDATE(), jatuh_tempo),0)) AS max_days_late,
           SUM(CASE WHEN status='LATE' THEN 1 ELSE 0 END)     AS late_count
    FROM jadwal_angsuran
    GROUP BY id_kontrak
  ) agg ON agg.id_kontrak = k.id_kontrak
  SET p.aktif = 0
  WHERE p.aktif = 1
    AND (agg.max_days_late >= 30 OR agg.late_count >= 2);
