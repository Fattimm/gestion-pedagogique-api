<?php

namespace App\Providers;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Storage;
use Kreait\Firebase\Firestore;
use Illuminate\Support\ServiceProvider;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Firestore::class, function ($app) {
            return $this->createFirebaseInstance()->createFirestore();
        });

        $this->app->singleton(Storage::class, function ($app) {
            return $this->createFirebaseInstance()->createStorage();
        });
    }

    public function boot()
    {
        //
    }

    private function createFirebaseInstance()
    {
        $firebasepath = base64_decode("ewogICAgInR5cGUiOiAic2VydmljZV9hY2NvdW50IiwKICAgICJwcm9qZWN0X2lkIjogImdlc3Rpb24tcGVkYWdvZ2lxdWUtOGNjMTciLAogICAgInByaXZhdGVfa2V5X2lkIjogImEyMGIwNDM1M2I0YWFjMTRiYWQzNGFjOTc4MGUxYjk3MGVhNjcyMGYiLAogICAgInByaXZhdGVfa2V5IjogIi0tLS0tQkVHSU4gUFJJVkFURSBLRVktLS0tLVxuTUlJRXZRSUJBREFOQmdrcWhraUc5dzBCQVFFRkFBU0NCS2N3Z2dTakFnRUFBb0lCQVFDeTZpSHRHdnlYd3RzTlxuUldqRDQxbUV1TDJaQWdyaEhDeG45dDZCNlBhdUxwQndmNkM0V3lYSWJ3NlZNYzNQWTFYWWo0eWowRVRuME9TNlxuWTdXemU0bmxwYnJPcFZjZSt1NWk2TG5NVVFKU0ZVSm1GUXhUSXd6SHJVSXdobVpXcytIQWlucmE4WUxIaWVXMlxucURhdnQvdWZudGZaUTZWS2hxaUYvdmJmOWJLQzBFeFlQSWQyZTduUFJnSURxczhtQlJ5QU93R2VyR0pGSWZzdlxuMlkzbUpQL3ZJVGlKaHBXK1NPS0x2ZGdoNER1b014eVJVRjRzcTYxc2xhUTZNZFJZS21qdGhmSUsxN0tlV2duSFxuMUFmN1Y0YXBlZjBLTmNVZjNPV3pQS3FPQTRUVnJVMXJBTnAyQTZrZy96ZllDSE00eXZ1YjlvZTljeXNUZ3hxdFxuME5lbE9NbVRBZ01CQUFFQ2dnRUFIUFBwb0pLYzdMd3lNYUFaVjVYYmZ6QmNNajRUMzZEdENMZjVBd0hkcFl0UVxuZ1h3TjJMZWJVaWV5cnU4YjFLTkpHWjRGVHQ4ZHYzdmMvOUt4N1VVaEtDSWRMR0t5U2dlbWV5Z1pKUmk0N09xV1xuejZrNVVOemJyUlo3SHhvb3hvQWVtTWR3SVMwekVqSmRFNlYrRUFxUWRDREM0bHhoaThGbDFTVlE4eVl5Q0p3Q1xuR2JGMm5RNnM0UXBYMXFvNFVuK2t1cUJ1RUVvd0xFQllKTVd5MkFoTjEwZXRjbEUrRWMyMktuMGg2OHJxUys3aFxuVHNQUU5VcU5Od2d3RkYxaktzR1U4T2FOUVBJb0pFaFNIYVpuQUUzVDNzV3ZseFpuZitDc0dUa2Q4MTI4SUtTT1xuRHRzQ05tcUQzRE9aakxRVEhSSTRJWHJsdldvMW9pem8wSHhWRGNxMHFRS0JnUURzaU9aUS85ZitaTHJ0eWk3bFxuanRYc1NlSjFYQlBMazQ1cTJremhSOTdDUnBwWXFMR0tuL2xDTWdiMkV6eGpwTnhoWFlFV2M5ZnlxWHNDT3N2UFxuQWpsUXVMUE5sb0VtTVY5ZXJmdk0yMmRDWDNvQnd5am1SSy95Tk9MMVplbmFKaGU4bDU1OWpoQTFXYVMrK05IcVxuZGN0b296Ly9oZ0F5WHlaUURTcERtVGdrR1FLQmdRREJvMWZNYVNuU2ttRGRJL2FWanpndnZDMXhzOXdJUkRGWVxuM0h5eEhVK3hzSTFzTWV1d0lLVkxXcTlPUllLMkw4d29xcWI3VWtUTE1zaGorUVBEdjRkL0RHblArMnM0a2Y4UVxuWXU5eXpwN090VnFzUkJHK2tIQmJ0clZiWmZtQW9LeWpIL01lanU5ZzFqOXVXakg5RWJTbjJMRldSMXVJTEl2dlxuSUpyYnI4eXdpd0tCZ0dHV3JlZVhWdWNuYWhRM2laZStrYlkyV1k5d0dONnlGMTB3aWUyY2VHU1JPcGIvcXBoMlxuSGlIWWdCVEFwUGE3aXcxRXhjQ2N0T2p5VWNUK3V4M0NYaUZXd3lBOFN6YVZ3akpPK0FkeU1pMHBOUzVLOWJIalxuZnZBZ0w4OXRaOGxRVkJURVBXaXgxekFidHlQdmhyUUVPNU1GWDU4dUN5QzlMeEp5REJEdHcwTHBBb0dBRTNCRlxuV1JUWmlQVCsybFJJNWJ5cDVFamN1d1JXTzFJMUxmbDhYdjlWUjc0MGhEOENyUHlwKzBXUVFhaEN0ZTFZYm1DclxuUUtHQ25HOXRwSE9VQ0N1Sys1c1FhRHVTQ096SEVTc05aSllQWlNyWmZsK2E5R2xsamg5cVYxR0dXWDVIdGx4M1xudUlLYXp6clN4VTR5cEhnSmphdjRLWFFpWVNvdnBnSzg2UDdkNm44Q2dZRUFsQ0x0NzRFaGZ0eFNkdk5ZWTkwSFxuZ0pUUjIzbXZSbmN2emd1Zmx6K3dsV0sycjczdWJZQ0JIS3ZEaGxuSzJiNlh3VXNOWVZJNHFreFlpbnh4eHMvdFxudG1XRkEvTXNoL0RPSFFHRmV0VWh1RG9PRjhKL0RGMi9KZ3RRZDhBdlM5NzlKeFkwQW9DV3piMVlpOHRWT2xQN1xuclFSbU4vTU1SVEx0ZXJkY0FWcmRyRG89XG4tLS0tLUVORCBQUklWQVRFIEtFWS0tLS0tXG4iLAogICAgImNsaWVudF9lbWFpbCI6ICJmaXJlYmFzZS1hZG1pbnNkay1yOWVrbkBnZXN0aW9uLXBlZGFnb2dpcXVlLThjYzE3LmlhbS5nc2VydmljZWFjY291bnQuY29tIiwKICAgICJjbGllbnRfaWQiOiAiMTE0NTM5MDIyMzU1NDYwNDkwNzE5IiwKICAgICJhdXRoX3VyaSI6ICJodHRwczovL2FjY291bnRzLmdvb2dsZS5jb20vby9vYXV0aDIvYXV0aCIsCiAgICAidG9rZW5fdXJpIjogImh0dHBzOi8vb2F1dGgyLmdvb2dsZWFwaXMuY29tL3Rva2VuIiwKICAgICJhdXRoX3Byb3ZpZGVyX3g1MDlfY2VydF91cmwiOiAiaHR0cHM6Ly93d3cuZ29vZ2xlYXBpcy5jb20vb2F1dGgyL3YxL2NlcnRzIiwKICAgICJjbGllbnRfeDUwOV9jZXJ0X3VybCI6ICJodHRwczovL3d3dy5nb29nbGVhcGlzLmNvbS9yb2JvdC92MS9tZXRhZGF0YS94NTA5L2ZpcmViYXNlLWFkbWluc2RrLXI5ZWtuJTQwZ2VzdGlvbi1wZWRhZ29naXF1ZS04Y2MxNy5pYW0uZ3NlcnZpY2VhY2NvdW50LmNvbSIsCiAgICAidW5pdmVyc2VfZG9tYWluIjogImdvb2dsZWFwaXMuY29tIgogIH0=", true);

        $factory = (new Factory)
            ->withServiceAccount(json_decode($firebasepath,true));

        $databaseUrl = 'https://gestion-pedagogique-8cc17.firebaseio.com';
        if ($databaseUrl) {
            $factory = $factory->withDatabaseUri($databaseUrl);
        }

        $storageBucket = env('FIREBASE_STORAGE_BUCKET');
        if ($storageBucket) {
            $factory = $factory->withDefaultStorageBucket($storageBucket);
        }

        return $factory;
    }
}
